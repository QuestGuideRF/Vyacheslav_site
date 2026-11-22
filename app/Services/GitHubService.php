<?php
namespace App\Services;
use App\Repositories\ProjectRepository;
use App\Repositories\SettingsRepository;
class GitHubService
{
    private SettingsRepository $settings;
    private ProjectRepository $projects;
    private string $cacheDir;
    public function __construct(
        SettingsRepository $settings,
        ProjectRepository $projects,
        string $cacheDir
    ) {
        $this->settings = $settings;
        $this->projects = $projects;
        $this->cacheDir = $cacheDir;
    }
    public function getDashboardData(bool $force = false): array
    {
        $username = trim((string) $this->settings->get('github_username', ''));
        if ($username === '') {
            return $this->emptyPayload();
        }
        $cacheFile = rtrim($this->cacheDir, '/\\') . '/github_' . $username . '_portfolio.json';
        $cached = $this->readCache($cacheFile);
        if ($cached !== null) {
            $cached = $this->attachWidgets($cached, $username);
        }
        if (!$force && $cached !== null && is_file($cacheFile) && filemtime($cacheFile) > (time() - 1800)) {
            return $cached;
        }
        $profile = $this->requestJson('https://api.github.com/users/' . rawurlencode($username));
        $reposResponse = $this->fetchOwnedRepos($username);
        $eventsResponse = $this->requestJson('https://api.github.com/users/' . rawurlencode($username) . '/events/public?per_page=100');
        $profileFailed = $profile === [] || $this->isApiError($profile);
        $reposFailed = $this->isApiError($reposResponse)
            || ($reposResponse === [] && (int) ($profile['public_repos'] ?? 0) > 0);
        $eventsFailed = $this->isApiError($eventsResponse);
        if ($profileFailed || $reposFailed || $eventsFailed) {
            $warning = 'GitHub API временно недоступен или достигнут лимит запросов. Показаны сохранённые данные.';
            if ($cached !== null) {
                $cached['meta'] = [
                    'warning' => $warning,
                    'cache_fallback' => true,
                ];
                return $cached;
            }
            $payload = $this->emptyPayload($username);
            $payload['meta'] = [
                'warning' => $warning,
                'cache_fallback' => false,
            ];
            if (!empty($profile['html_url'])) {
                $payload['profile']['html_url'] = (string) $profile['html_url'];
            }
            return $payload;
        }
        $repos = $this->normalizeRepos($this->ensureList($reposResponse));
        $events = $this->ensureList($eventsResponse);
        $sourceRepos = $repos;
        $languageCounts = [];
        $totalStars = 0;
        $activeProjectsCount = 0;
        $activeRepos = [];
        foreach ($sourceRepos as $repo) {
            $totalStars += (int) ($repo['stargazers_count'] ?? 0);
            if ($this->isRecentlyActive((string) ($repo['pushed_at'] ?? $repo['updated_at'] ?? ''), 365) && empty($repo['archived'])) {
                $activeProjectsCount++;
            }
            $activeRepos[] = [
                'name' => $repo['name'] ?? '',
                'description' => $repo['description'] ?? '',
                'html_url' => $repo['html_url'] ?? '',
                'homepage' => $repo['homepage'] ?? '',
                'language' => $repo['language'] ?? '',
                'updated_at' => $repo['updated_at'] ?? '',
                'stargazers_count' => (int) ($repo['stargazers_count'] ?? 0),
            ];
            $primaryLanguage = trim((string) ($repo['language'] ?? ''));
            if ($primaryLanguage !== '') {
                $languageCounts[$primaryLanguage] = ($languageCounts[$primaryLanguage] ?? 0) + 1;
            }
        }
        usort($activeRepos, static function (array $left, array $right): int {
            return strcmp((string) ($right['updated_at'] ?? ''), (string) ($left['updated_at'] ?? ''));
        });
        arsort($languageCounts);
        $totalLanguages = array_sum($languageCounts) ?: 1;
        $languageShare = [];
        foreach (array_slice($languageCounts, 0, 6, true) as $language => $count) {
            $languageShare[] = [
                'name' => $language,
                'percent' => round(($count / $totalLanguages) * 100, 1),
            ];
        }
        $profileData = [
            'login' => $profile['login'] ?? $username,
            'name' => $profile['name'] ?? $username,
            'avatar_url' => $profile['avatar_url'] ?? '',
            'html_url' => $profile['html_url'] ?? 'https://github.com/' . $username,
            'bio' => $profile['bio'] ?? '',
            'public_repos' => (int) ($profile['public_repos'] ?? count($repos)),
            'followers' => (int) ($profile['followers'] ?? 0),
            'following' => (int) ($profile['following'] ?? 0),
        ];
        $statsData = [
            'public_repos' => (int) ($profile['public_repos'] ?? count($repos)),
            'stars' => $totalStars,
            'followers' => (int) ($profile['followers'] ?? 0),
            'active_projects' => $activeProjectsCount,
        ];
        $normalizedEvents = $this->filterEvents($events, $sourceRepos);
        $payload = [
            'profile' => $profileData,
            'stats' => $statsData,
            'languages' => $languageShare,
            'repos' => array_slice($activeRepos, 0, 6),
            'events' => array_slice($normalizedEvents, 0, 6),
            'widgets' => $this->buildWidgets($username),
            'widget_data' => $this->buildWidgetData($profileData, $statsData, $languageShare, $normalizedEvents),
            'updated_at' => date('c'),
            'meta' => [],
        ];
        $this->writeCache($cacheFile, $payload);
        return $payload;
    }
    private function loadPortfolioRepos(string $username, array $repos): array
    {
        $slugs = $this->loadPortfolioSlugs($username);
        if ($slugs === []) {
            return [];
        }
        $result = [];
        $seen = [];
        $indexed = [];
        foreach ($repos as $repo) {
            $fullName = strtolower((string) ($repo['full_name'] ?? ''));
            if ($fullName !== '') {
                $indexed[$fullName] = $repo;
            }
        }
        foreach ($slugs as $slug) {
            $slugKey = strtolower($slug);
            if (isset($seen[$slugKey])) {
                continue;
            }
            if (isset($indexed[$slugKey])) {
                $seen[$slugKey] = true;
                $result[] = $indexed[$slugKey];
                continue;
            }
            $repo = $this->requestJson($this->buildRepoApiUrl($slug));
            if ($this->isApiError($repo) || empty($repo['full_name'])) {
                continue;
            }
            $seen[$slugKey] = true;
            $result[] = $repo;
        }
        return $this->normalizeRepos($result);
    }
    private function loadPortfolioSlugs(string $username): array
    {
        $result = [];
        $seen = [];
        foreach ($this->projects->featured(12) as $project) {
            $repoUrl = trim((string) ($project['repo_url'] ?? ''));
            if ($repoUrl === '') {
                continue;
            }
            $slug = $this->extractRepoSlug($repoUrl, $username);
            if ($slug === null) {
                continue;
            }
            $slugKey = strtolower($slug);
            if (isset($seen[$slugKey])) {
                continue;
            }
            $seen[$slugKey] = true;
            $result[] = $slug;
        }
        return $result;
    }
    private function extractRepoSlug(string $url, string $defaultOwner): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        if (count($parts) >= 2) {
            return $parts[0] . '/' . preg_replace('/\.git$/', '', $parts[1]);
        }
        if (count($parts) === 1) {
            return $defaultOwner . '/' . preg_replace('/\.git$/', '', $parts[0]);
        }
        return null;
    }
    private function normalizeRepos(array $repos): array
    {
        $result = [];
        foreach ($repos as $repo) {
            if (!is_array($repo) || !empty($repo['fork'])) {
                continue;
            }
            $result[] = $repo;
        }
        return $result;
    }
    private function filterEvents(array $events, array $sourceRepos): array
    {
        $allowed = [];
        foreach ($sourceRepos as $repo) {
            if (!empty($repo['full_name'])) {
                $allowed[(string) $repo['full_name']] = true;
            }
        }
        $normalized = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            $normalized[] = $this->normalizeEvent($event);
        }
        if (!$allowed) {
            return $normalized;
        }
        $filtered = array_values(array_filter($normalized, static function (array $event) use ($allowed): bool {
            return isset($allowed[$event['repo']]);
        }));
        return $filtered ?: $normalized;
    }
    private function normalizeEvent(array $event): array
    {
        $type = (string) ($event['type'] ?? 'Activity');
        $repo = $event['repo']['name'] ?? '';
        return [
            'type' => $type,
            'repo' => $repo,
            'created_at' => $event['created_at'] ?? '',
        ];
    }
    private function emptyPayload(string $username = ''): array
    {
        $profileUrl = $username !== '' ? 'https://github.com/' . $username : '';
        return [
            'profile' => [
                'login' => $username,
                'name' => $username,
                'avatar_url' => '',
                'html_url' => $profileUrl,
                'bio' => '',
                'public_repos' => 0,
                'followers' => 0,
                'following' => 0,
            ],
            'stats' => [
                'public_repos' => 0,
                'stars' => 0,
                'followers' => 0,
                'active_projects' => 0,
            ],
            'languages' => [],
            'repos' => [],
            'events' => [],
            'widgets' => $this->buildWidgets($username),
            'widget_data' => $this->buildWidgetData([], [], [], []),
            'updated_at' => date('c'),
            'meta' => [],
        ];
    }
    private function attachWidgets(array $payload, string $username): array
    {
        $payload['widgets'] = $this->buildWidgets($username);
        $payload['widget_data'] = $this->buildWidgetData(
            is_array($payload['profile'] ?? null) ? $payload['profile'] : [],
            is_array($payload['stats'] ?? null) ? $payload['stats'] : [],
            is_array($payload['languages'] ?? null) ? $payload['languages'] : [],
            is_array($payload['events'] ?? null) ? $payload['events'] : []
        );
        return $payload;
    }
    private function buildWidgets(string $username): array
    {
        $username = trim($username);
        if ($username === '') {
            return [];
        }
        $base = url('/github/widget');
        return [
            'stats' => [
                'dark' => $base . '/stats/dark',
                'light' => $base . '/stats/light',
            ],
            'languages' => [
                'dark' => $base . '/languages/dark',
                'light' => $base . '/languages/light',
            ],
            'streak' => [
                'dark' => $base . '/streak/dark',
                'light' => $base . '/streak/light',
            ],
            'activity' => [
                'dark' => $base . '/activity/dark',
                'light' => $base . '/activity/light',
            ],
            'trophy' => [
                'dark' => $base . '/trophy/dark',
                'light' => $base . '/trophy/light',
            ],
        ];
    }
    private function buildWidgetData(array $profile, array $stats, array $languages, array $events): array
    {
        $activity = $this->buildActivityData($events);
        $streak = $this->buildStreakData($activity);
        return [
            'summary' => [
                'name' => (string) ($profile['name'] ?? $profile['login'] ?? 'GitHub'),
                'login' => (string) ($profile['login'] ?? ''),
                'bio' => (string) ($profile['bio'] ?? ''),
                'repos' => (int) ($stats['public_repos'] ?? 0),
                'stars' => (int) ($stats['stars'] ?? 0),
                'followers' => (int) ($stats['followers'] ?? 0),
                'active_projects' => (int) ($stats['active_projects'] ?? 0),
                'following' => (int) ($profile['following'] ?? 0),
            ],
            'languages' => array_slice($languages, 0, 5),
            'activity' => $activity,
            'streak' => $streak,
            'achievements' => $this->buildAchievements($profile, $stats, $languages, $activity, $streak),
        ];
    }
    private function buildActivityData(array $events, int $days = 28): array
    {
        $today = new \DateTimeImmutable('today');
        $start = $today->modify('-' . ($days - 1) . ' days');
        $counts = [];
        for ($index = 0; $index < $days; $index++) {
            $date = $start->modify('+' . $index . ' days');
            $key = $date->format('Y-m-d');
            $counts[$key] = 0;
        }
        foreach ($events as $event) {
            if (!is_array($event) || empty($event['created_at'])) {
                continue;
            }
            $timestamp = strtotime((string) $event['created_at']);
            if ($timestamp === false) {
                continue;
            }
            $key = date('Y-m-d', $timestamp);
            if (array_key_exists($key, $counts)) {
                $counts[$key]++;
            }
        }
        $max = max($counts ?: [0]);
        $daysData = [];
        foreach ($counts as $date => $count) {
            $level = $max > 0 ? (int) ceil(($count / $max) * 4) : 0;
            $daysData[] = [
                'date' => $date,
                'label' => date('d.m', strtotime($date)) ?: $date,
                'count' => $count,
                'level' => min($level, 4),
            ];
        }
        return [
            'days' => $daysData,
            'max' => $max,
            'total' => array_sum($counts),
            'active_days' => count(array_filter($counts, static fn (int $count): bool => $count > 0)),
        ];
    }
    private function buildStreakData(array $activity): array
    {
        $days = is_array($activity['days'] ?? null) ? $activity['days'] : [];
        if ($days === []) {
            return [
                'current' => 0,
                'best' => 0,
                'active_days' => 0,
                'last_active' => '',
                'events_total' => 0,
            ];
        }
        $best = 0;
        $running = 0;
        $current = 0;
        $lastActive = '';
        foreach ($days as $item) {
            $count = (int) ($item['count'] ?? 0);
            if ($count > 0) {
                $running++;
                $best = max($best, $running);
                $lastActive = (string) ($item['date'] ?? $lastActive);
            } else {
                $running = 0;
            }
        }
        for ($index = count($days) - 1; $index >= 0; $index--) {
            $count = (int) ($days[$index]['count'] ?? 0);
            if ($count <= 0) {
                if ($current > 0) {
                    break;
                }
                continue;
            }
            $current++;
        }
        return [
            'current' => $current,
            'best' => $best,
            'active_days' => (int) ($activity['active_days'] ?? 0),
            'last_active' => $lastActive,
            'events_total' => (int) ($activity['total'] ?? 0),
        ];
    }
    private function buildAchievements(array $profile, array $stats, array $languages, array $activity, array $streak): array
    {
        $topLanguage = (string) ($languages[0]['name'] ?? 'Mixed');
        $topLanguageShare = (float) ($languages[0]['percent'] ?? 0);
        return [
            [
                'title' => 'Builder',
                'value' => (int) ($stats['public_repos'] ?? 0) . ' repos',
                'description' => 'Публичные репозитории, опубликованные на GitHub.',
            ],
            [
                'title' => 'Momentum',
                'value' => (int) ($streak['current'] ?? 0) . ' дневная полоса',
                'description' => 'Текущая полоса общественной активности',
            ],
            [
                'title' => 'Community',
                'value' => (int) ($profile['followers'] ?? 0) . ' followers',
                'description' => 'Люди, отслеживающие профиль и проекты',
            ],
            [
                'title' => 'Основной стек',
                'value' => $topLanguage,
                'description' => ($topLanguageShare > 0 ? number_format($topLanguageShare, 1) . '% ' : '') . 'доля отслеживаемого кода',
            ],
        ];
    }
    public function renderWidget(string $type, string $theme): string
    {
        $type = strtolower(trim($type));
        $theme = strtolower(trim($theme)) === 'light' ? 'light' : 'dark';
        $payload = $this->getDashboardData();
        $widgetData = is_array($payload['widget_data'] ?? null) ? $payload['widget_data'] : [];
        return match ($type) {
            'stats' => $this->renderStatsWidget($widgetData['summary'] ?? [], $theme),
            'languages' => $this->renderLanguagesWidget($widgetData['languages'] ?? [], $theme),
            'streak' => $this->renderStreakWidget($widgetData['streak'] ?? [], $theme),
            'activity' => $this->renderActivityWidget($widgetData['activity'] ?? [], $theme),
            'trophy' => $this->renderTrophyWidget($widgetData['achievements'] ?? [], $theme),
            default => $this->renderFallbackWidget('Виджет не найден', $theme, 495, 195),
        };
    }
    private function renderStatsWidget(array $summary, string $theme): string
    {
        $palette = $this->widgetPalette($theme);
        $name = $this->svgText((string) ($summary['name'] ?? 'GitHub'));
        $login = $this->svgText('@' . (string) ($summary['login'] ?? ''));
        $bio = $this->svgText($this->limitText((string) ($summary['bio'] ?? 'Реальные данные профиля GitHub отражены на этом сайте.'), 84));
        $cards = [
            ['Repos', (string) (int) ($summary['repos'] ?? 0)],
            ['Stars', (string) (int) ($summary['stars'] ?? 0)],
            ['Followers', (string) (int) ($summary['followers'] ?? 0)],
            ['Projects', (string) (int) ($summary['active_projects'] ?? 0)],
        ];
        $boxes = '';
        foreach ($cards as $index => [$label, $value]) {
            $x = 24 + (($index % 2) * 225);
            $y = 96 + (int) floor($index / 2) * 48;
            $boxes .= '<прямой х="' . $x . '" y="' . $y . '" width="205" height="36" rx="12" fill="' . $palette['panel'] . '" инсульт="' . $palette['line'] . '"/>';
            $boxes .= '<текст х="' . ($x + 14) . '" y="' . ($y + 14) . '"заполнить="' . $palette['muted'] . '" font-size="10" font-family="IBM Plex Sans, без засечек">' . $this->svgText($label) . '</текст>';
            $boxes .= '<текст х="' . ($x + 14) . '" y="' . ($y + 28) . '"заполнить="' . $palette['text'] . '" Font-size="16" Font-weight="700" Font-Family="IBM Plex Sans, без засечек">' . $this->svgText($value) . '</текст>';
        }
        return $this->renderSvgFrame(495, 195, $theme, 'Сводка по аккаунту', '
            <text x="24" y="34" fill="' . $palette['muted'] . '" font-size="11" font-family="JetBrains Mono, monospace">Live / Account</text>
            <text x="24" y="60" fill="' . $palette['text'] . '" Font-size="24" Font-weight="700" Font-Family="IBM Plex Sans, без засечек">' . $name . '</text>
            <text x="471" y="34" text-anchor="end" fill="' . $palette['accent'] . '" font-size="11" font-family="JetBrains Mono, monospace">' . $login . '</text>
            <text x="24" y="80" fill="' . $palette['muted'] . '" font-size="11" font-family="IBM Plex Sans, без засечек">' . $bio . '</текст>
            ' . $boxes . '
        ');
    }
    private function renderLanguagesWidget(array $languages, string $theme): string
    {
        $palette = $this->widgetPalette($theme);
        $bars = '';
        if ($languages === []) {
            $bars = '<text x="24" y="112" fill="' . $palette['muted'] . '" font-size="13" font-family="IBM Plex Sans, sans-serif">Данных о языке пока нет.</text>';
        } else {
            foreach (array_values($languages) as $index => $language) {
                $name = $this->svgText((string) ($language['name'] ?? 'Code'));
                $percent = (float) ($language['percent'] ?? 0);
                $y = 76 + ($index * 24);
                $width = max(10, (int) round(360 * min($percent, 100) / 100));
                $bars .= '<текст x="24" y="' . $y . '"заполнить="' . $palette['text'] . '" Font-size="12" Font-weight="600" Font-Family="IBM Plex Sans, без засечек">' . $name . '</текст>';
                $bars .= '<текст x="471" y="' . $y . '" text-anchor="конец" fill="' . $palette['muted'] . '" font-size="12" font-family="JetBrains Mono, monospace">' . $this->svgText(number_format($percent, 1) . '%') . '</текст>';
                $bars .= '<прямой x="24" y="' . ($y + 8) . '" width="447" height="8" rx="4" fill="' . $palette['panel'] . '"/>';
                $bars .= '<прямой x="24" y="' . ($y + 8) . '"ширина="' . $width . '" height="8" rx="4" fill="' . $palette['accent'] . '"/>';
            }
        }
        return $this->renderSvgFrame(495, 195, $theme, 'Languages', '
            <text x="24" y="34" fill="' . $palette['muted'] . '" font-size="11" font-family="JetBrains Mono, monospace">Live / Stack</text>
            <text x="24" y="60" fill="' . $palette['text'] . '" font-size="22" font-weight="700" font-family="IBM Plex Sans, sans-serif">Языки учетной записи</text>
            ' . $bars . '
        ');
    }
    private function renderStreakWidget(array $streak, string $theme): string
    {
        $palette = $this->widgetPalette($theme);
        $current = (int) ($streak['current'] ?? 0);
        $best = (int) ($streak['best'] ?? 0);
        $activeDays = (int) ($streak['active_days'] ?? 0);
        $eventsTotal = (int) ($streak['events_total'] ?? 0);
        $lastActive = $this->svgText($streak['last_active'] !== '' ? date('d.m.Y', strtotime((string) $streak['last_active'])) : 'Нет недавних событий');
        return $this->renderSvgFrame(495, 195, $theme, 'Streak', '
            <text x="24" y="34" fill="' . $palette['muted'] . '" font-size="11" font-family="JetBrains Mono, monospace">Live / Streak</text>
            <text x="24" y="60" fill="' . $palette['text'] . '" font-size="22" font-weight="700" font-family="IBM Plex Sans, sans-serif">Contribution streak</text>
            <text x="24" y="106" fill="' . $palette['accent'] . '" Font-size="40" Font-weight="700" Font-Family="IBM Plex Sans, без засечек">' . $current . '</text>
            <text x="24" y="126" fill="' . $palette['muted'] . '" font-size="12" font-family="JetBrains Mono, monospace">current days</text>
            <text x="265" y="106" fill="' . $palette['text'] . '" Font-size="34" Font-weight="700" Font-Family="IBM Plex Sans, без засечек">' . $best . '</text>
            <text x="265" y="126" fill="' . $palette['muted'] . '" font-size="12" font-family="JetBrains Mono, monospace">best in last 28 days</text>
            <rect x="24" y="144" width="447" height="27" rx="12" fill="' . $palette['panel'] . '" инсульт="' . $palette['line'] . '"/>
            <text x="38" y="161" fill="' . $palette['muted'] . '" font-size="11" font-family="IBM Plex Sans, sans-serif">Дни активности: ' . $activeDays . '  |  События: ' . $eventsTotal . '</text>
            <text x="471" y="161" text-anchor="end" fill="' . $palette['accent2'] . '" font-size="11" font-family="JetBrains Mono, monospace">' . $lastActive . '</текст>
        ');
    }
    private function renderActivityWidget(array $activity, string $theme): string
    {
        $palette = $this->widgetPalette($theme);
        $days = is_array($activity['days'] ?? null) ? $activity['days'] : [];
        $max = max((int) ($activity['max'] ?? 0), 1);
        $bars = '';
        $barWidth = 20;
        $gap = 6;
        $baseX = 24;
        $baseY = 214;
        foreach ($days as $index => $item) {
            $count = (int) ($item['count'] ?? 0);
            $height = $count > 0 ? max(8, (int) round(($count / $max) * 108)) : 4;
            $x = $baseX + ($index * ($barWidth + $gap));
            $y = $baseY - $height;
            $fill = $count > 0 ? $palette['accent'] : $palette['panel'];
            $bars .= '<прямой х="' . $x . '" y="' . $y . '"ширина="' . $barWidth . '"высота="' . $height . '" rx="6" fill="' . $fill . '"/>';
            if ($index % 7 === 0) {
                $bars .= '<текст х="' . ($x + 10) . '" y="238" text-anchor="middle" fill="' . $palette['muted'] . '" font-size="10" font-family="JetBrains Mono, monospace">' . $this->svgText((string) ($item['label'] ?? '')) . '</текст>';
            }
        }
        return $this->renderSvgFrame(800, 260, $theme, 'Activity', '
            <text x="24" y="34" fill="' . $palette['muted'] . '" font-size="11" font-family="JetBrains Mono, monospace">Live / Activity</text>
            <text x="24" y="60" fill="' . $palette['text'] . '" font-size="24" font-weight="700" font-family="IBM Plex Sans, sans-serif">Last 28 days</text>
            <text x="776" y="34" text-anchor="end" fill="' . $palette['accent'] . '" font-size="11" font-family="JetBrains Mono, monospace">events ' . (int) ($activity['total'] ?? 0) . '</text>
            <text x="24" y="84" fill="' . $palette['muted'] . '" font-size="12" font-family="IBM Plex Sans, sans-serif">Public GitHub activity rendered directly on domninvyacheslav.ru</text>
            <line x1="24" y1="214" x2="776" y2="214" stroke="' . $palette['line'] . '" ширина-штриха="1"/>
            ' . $bars . '
        ');
    }
    private function renderTrophyWidget(array $achievements, string $theme): string
    {
        $palette = $this->widgetPalette($theme);
        $cards = '';
        foreach (array_values(array_slice($achievements, 0, 4)) as $index => $achievement) {
            $x = 24 + (($index % 2) * 376);
            $y = 74 + ((int) floor($index / 2) * 62);
            $title = $this->svgText($this->limitText((string) ($achievement['title'] ?? 'Achievement'), 18));
            $value = $this->svgText($this->limitText((string) ($achievement['value'] ?? ''), 20));
            $description = $this->svgText($this->limitText((string) ($achievement['description'] ?? ''), 42));
            $cards .= '<прямой х="' . $x . '" y="' . $y . '" width="352" height="48" rx="16" fill="' . $palette['panel'] . '" инсульт="' . $palette['line'] . '"/>';
            $cards .= '<текст х="' . ($x + 16) . '" y="' . ($y + 18) . '"заполнить="' . $palette['muted'] . '" font-size="10" font-family="JetBrains Mono, monospace">' . $title . '</текст>';
            $cards .= '<текст х="' . ($x + 16) . '" y="' . ($y + 33) . '"заполнить="' . $palette['text'] . '" Font-size="16" Font-weight="700" Font-Family="IBM Plex Sans, без засечек">' . $value . '</текст>';
            $cards .= '<текст х="' . ($x + 336) . '" y="' . ($y + 33) . '" text-anchor="конец" fill="' . $palette['accent'] . '" font-size="10" font-family="IBM Plex Sans, без засечек">' . $description . '</текст>';
        }
        return $this->renderSvgFrame(800, 220, $theme, 'Achievements', '
            <text x="24" y="34" fill="' . $palette['muted'] . '" font-size="11" font-family="JetBrains Mono, monospace">Live / Achievements</text>
            <text x="24" y="60" fill="' . $palette['text'] . '" font-size="24" font-weight="700" font-family="IBM Plex Sans, sans-serif">Достижения профиля</text>
            ' . $cards . '
        ');
    }
    private function renderFallbackWidget(string $title, string $theme, int $width, int $height): string
    {
        $palette = $this->widgetPalette($theme);
        return $this->renderSvgFrame($width, $height, $theme, $title, '
            <text x="24" y="60" fill="' . $palette['text'] . '" Font-size="24" Font-weight="700" Font-Family="IBM Plex Sans, без засечек">' . $this->svgText($title) . '</text>
            <text x="24" y="90" fill="' . $palette['muted'] . '" font-size="13" font-family="IBM Plex Sans, sans-serif">Виджет временно недоступен.</text>
        ');
    }
    private function renderSvgFrame(int $width, int $height, string $theme, string $title, string $content): string
    {
        $palette = $this->widgetPalette($theme);
        $gradientId = 'grad-' . $theme . '-' . substr(sha1($title . $width . $height), 0, 8);
        return '<?xml версия="1.0" кодировка="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '"высота="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" role="img" aria-label="' . $this->svgText($title) . '">'
            . '<defs><linearGradient id="' . $gradientId . '" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0%" стоп-цвет="' . $palette['glow'] . '" стоп-opacity="0.16"/>'
            . '<stop offset="100%" stop-color="' . $palette['glow2'] . '" стоп-opacity="0.05"/>'
            . '</linearGradient></defs>'
            . '<прямая ширина="' . $width . '"высота="' . $height . '" rx="28" fill="' . $palette['bg'] . '"/>'
            . '<rect x="1" y="1" ширина="' . ($width - 2) . '"высота="' . ($height - 2) . '" rx="27" fill="url(#' . $gradientId . ')" штрих="' . $palette['line'] . '"/>'
            . $content
            . '</svg>';
    }
    private function widgetPalette(string $theme): array
    {
        if ($theme === 'light') {
            return [
                'bg' => '#F7F9FC',
                'panel' => '#EEF3F8',
                'line' => '#D7DEE8',
                'text' => '#111824',
                'muted' => '#5B687D',
                'accent' => '#11767E',
                'accent2' => '#7C8F2A',
                'glow' => '#11767E',
                'glow2' => '#C6D77A',
            ];
        }
        return [
            'bg' => '#0C111B',
            'panel' => '#141C2A',
            'line' => '#253044',
            'text' => '#EDF1FB',
            'muted' => '#91A0BC',
            'accent' => '#7CE7D6',
            'accent2' => '#C6F27B',
            'glow' => '#7CE7D6',
            'glow2' => '#C6F27B',
        ];
    }
    private function svgText(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    private function limitText(string $text, int $limit): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, max($limit - 1, 1), 'UTF-8'), " \t\n\r\0\x0B.,") . '…';
    }
    private function fetchOwnedRepos(string $username): array
    {
        $result = [];
        $page = 1;
        do {
            $chunk = $this->requestJson(
                'https://api.github.com/users/' . rawurlencode($username) . '/repos?type=owner&sort=updated&per_page=100&page=' . $page
            );
            if ($this->isApiError($chunk)) {
                return $chunk;
            }
            if (!$this->isList($chunk)) {
                return [];
            }
            $result = array_merge($result, $chunk);
            $page++;
            $count = count($chunk);
        } while ($count === 100);
        return $result;
    }
    private function isRecentlyActive(string $date, int $days = 365): bool
    {
        if ($date === '') {
            return false;
        }
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return false;
        }
        return $timestamp >= (time() - ($days * 86400));
    }
    private function readCache(string $cacheFile): ?array
    {
        if (!is_file($cacheFile)) {
            return null;
        }
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        return is_array($cached) ? $cached : null;
    }
    private function writeCache(string $cacheFile, array $payload): void
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0775, true);
        }
        file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
    private function ensureList(array $payload): array
    {
        return $this->isList($payload) ? $payload : [];
    }
    private function isList(array $payload): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($payload);
        }
        return array_keys($payload) === range(0, count($payload) - 1);
    }
    private function isApiError(array $payload): bool
    {
        return isset($payload['message']) && is_string($payload['message']);
    }
    private function isLanguageMap(array $payload): bool
    {
        if ($payload === [] || $this->isList($payload) || $this->isApiError($payload)) {
            return false;
        }
        foreach ($payload as $language => $bytes) {
            if (!is_string($language) || !is_numeric($bytes)) {
                return false;
            }
        }
        return true;
    }
    private function buildRepoApiUrl(string $slug): string
    {
        [$owner, $repo] = explode('/', $slug, 2);
        return 'https://api.github.com/repos/' . rawurlencode($owner) . '/' . rawurlencode($repo);
    }
    private function requestJson(string $url): array
    {
        $headers = [
            'Пользовательский агент: domninvyacheslav-portfolio/1.0',
            'Примите: приложение/vnd.github+json',
        ];
        $token = trim((string) $this->settings->get('github_token', ''));
        if ($token !== '') {
            $headers[] = 'Разрешение: на предъявителя ' . $token;
        }
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => $headers,
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => implode("\r\n", $headers),
                    'timeout' => 20,
                ],
            ]);
            $result = @file_get_contents($url, false, $context);
        }
        if (!is_string($result) || $result === '') {
            return [];
        }
        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : [];
    }
}