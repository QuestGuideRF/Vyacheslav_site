(() => {
    const doc = document.documentElement;
    const config = window.APP_CONFIG || { theme: "dark" };
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const hasFinePointer = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    const largeViewport = window.matchMedia("(min-width: 768px)").matches;
    const enablePointerEffects = !reduced && hasFinePointer;
    const enableAmbientCanvas = !reduced && largeViewport;
    const setTheme = (theme) => {
        const nextTheme = theme === "light" ? "light" : "dark";
        doc.setAttribute("data-theme", nextTheme);
        try {
            localStorage.setItem("domnin-theme", nextTheme);
        } catch (error) {}
    };
    setTheme(doc.getAttribute("data-theme") || config.theme || "dark");
    document.querySelector(".theme-toggle")?.addEventListener("click", () => {
        setTheme(doc.getAttribute("data-theme") === "dark" ? "light" : "dark");
    });
    const reveals = [...document.querySelectorAll(".reveal")];
    reveals.forEach((item) => item.style.setProperty("--delay", item.dataset.delay || "0"));
    if (reduced) {
        reveals.forEach((item) => item.classList.add("is-visible"));
    } else if (reveals.length) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.14 });
        reveals.forEach((item) => revealObserver.observe(item));
    }
    const runWhenIdle = (callback) => {
        if ("requestIdleCallback" in window) {
            window.requestIdleCallback(() => callback(), { timeout: 1200 });
            return;
        }
        setTimeout(callback, 320);
    };
    const initNavTracking = () => {
        const sections = [...document.querySelectorAll(".section[id]")];
        const navLinks = [...document.querySelectorAll("[data-nav-link]")];
        if (!sections.length || !navLinks.length) {
            return;
        }
        const syncActiveNav = (id) => {
            navLinks.forEach((link) => {
                link.classList.toggle("is-active", link.getAttribute("href") === `#${id}`);
            });
        };
        syncActiveNav(sections[0]?.id || "");
        const sectionObserver = new IntersectionObserver((entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((left, right) => right.intersectionRatio - left.intersectionRatio)[0];
            if (visible?.target?.id) {
                syncActiveNav(visible.target.id);
            }
        }, {
            rootMargin: "-28% 0px -52% 0px",
            threshold: [0.2, 0.45, 0.7],
        });
        sections.forEach((section) => sectionObserver.observe(section));
    };
    const initScrollProgress = () => {
        const progress = document.querySelector(".scroll-progress span");
        if (!progress) {
            return;
        }
        let progressFrame = 0;
        const updateScrollProgress = () => {
            progressFrame = 0;
            const root = document.scrollingElement || doc;
            const maxScroll = Math.max(root.scrollHeight - root.clientHeight, 0);
            const ratio = maxScroll > 0 ? root.scrollTop / maxScroll : 0;
            progress.style.transform = `scaleX(${Math.min(Math.max(ratio, 0), 1).toFixed(4)})`;
        };
        const requestProgressUpdate = () => {
            if (progressFrame) {
                return;
            }
            progressFrame = requestAnimationFrame(updateScrollProgress);
        };
        requestProgressUpdate();
        window.addEventListener("scroll", requestProgressUpdate, { passive: true });
        window.addEventListener("resize", requestProgressUpdate, { passive: true });
    };
    const initSectionAnimations = () => {
        const sections = [...document.querySelectorAll(".section")];
        if (sections.length) {
            if (reduced) {
                sections.forEach((section) => section.classList.add("is-active-section"));
            } else {
                const sectionObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        entry.target.classList.toggle("is-active-section", entry.isIntersecting);
                    });
                }, {
                    rootMargin: "-10% 0px -18% 0px",
                    threshold: [0.08, 0.2, 0.45],
                });
                sections.forEach((section) => sectionObserver.observe(section));
            }
        }
        if (reduced || !largeViewport) {
            return;
        }
        const selectors = [
            ".section-head",
            ".glass-card",
            ".service-card",
            ".timeline-card",
            ".stack-card",
            ".seo-card",
            ".github-main",
            ".github-languages",
            ".post-card",
            ".contact-form",
            ".contact-item",
            ".trajectory-step",
        ];
        const elements = [...document.querySelectorAll(selectors.join(","))];
        if (!elements.length) {
            return;
        }
        const getStrength = (element) => {
            if (element.classList.contains("section-head")) {
                return 18;
            }
            if (element.classList.contains("github-main") || element.classList.contains("glass-card")) {
                return 12;
            }
            return 9;
        };
        const items = elements.map((element) => {
            element.classList.add("scroll-drift");
            return {
                element,
                strength: getStrength(element),
            };
        });
        const active = new Set();
        let frame = 0;
        const updateDrift = () => {
            frame = 0;
            active.forEach((item) => {
                const rect = item.element.getBoundingClientRect();
                const center = rect.top + rect.height / 2;
                const progressValue = ((window.innerHeight * 0.52) - center) / window.innerHeight;
                const drift = Math.max(Math.min(progressValue * item.strength, item.strength), -item.strength);
                item.element.style.setProperty("--drift-y", `${drift.toFixed(2)}px`);
            });
        };
        const requestDriftUpdate = () => {
            if (frame) {
                return;
            }
            frame = requestAnimationFrame(updateDrift);
        };
        const driftObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const item = entry.target.__scrollDrift;
                if (!item) {
                    return;
                }
                if (entry.isIntersecting) {
                    active.add(item);
                } else {
                    active.delete(item);
                    item.element.style.setProperty("--drift-y", "0px");
                }
            });
            requestDriftUpdate();
        }, {
            rootMargin: "18% 0px 18% 0px",
            threshold: [0, 1],
        });
        items.forEach((item) => {
            item.element.__scrollDrift = item;
            driftObserver.observe(item.element);
        });
        requestDriftUpdate();
        window.addEventListener("scroll", requestDriftUpdate, { passive: true });
        window.addEventListener("resize", requestDriftUpdate, { passive: true });
    };
    const initPointerEffects = () => {
        if (!enablePointerEffects) {
            return;
        }
        document.querySelectorAll(".tilt-card").forEach((card) => {
            card.addEventListener("mousemove", (event) => {
                const rect = card.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(1200px) rotateX(${(-y * 6).toFixed(2)}deg) rotateY(${(x * 6).toFixed(2)}deg) translateY(-2px)`;
            });
            card.addEventListener("mouseleave", () => {
                card.style.transform = "";
            });
        });
        document.querySelectorAll(".magnetic").forEach((button) => {
            button.addEventListener("mousemove", (event) => {
                const rect = button.getBoundingClientRect();
                const x = event.clientX - rect.left - rect.width / 2;
                const y = event.clientY - rect.top - rect.height / 2;
                button.style.transform = `translate(${x * 0.08}px, ${y * 0.08}px)`;
            });
            button.addEventListener("mouseleave", () => {
                button.style.transform = "";
            });
        });
    };
    const initCounters = () => {
        const counters = [...document.querySelectorAll("[data-counter]")];
        if (reduced || !counters.length) {
            return;
        }
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                const el = entry.target;
                const target = Number(el.textContent || 0);
                const startedAt = performance.now();
                const tick = (now) => {
                    const progressValue = Math.min((now - startedAt) / 1000, 1);
                    el.textContent = String(Math.round(target * progressValue));
                    if (progressValue < 1) {
                        requestAnimationFrame(tick);
                    }
                };
                requestAnimationFrame(tick);
                observer.unobserve(el);
            });
        }, { threshold: 0.55 });
        counters.forEach((counter) => counterObserver.observe(counter));
    };
    const initTypewriter = () => {
        const codeTarget = document.querySelector("#heroCode");
        if (!codeTarget || reduced || !largeViewport) {
            return;
        }
        const samples = [
`const profile = {
  focus: ["PHP", "JavaScript", "Python", "C++"],
  build: "real digital products"
};`,
`function syncGithub() {
  return fetch("/github/api")
    .then(updatePortfolioStats);
}`,
`class ProductBuilder {
  ship() { return "Fitodomik / QuestGuideRF"; }
}`
        ];
        let sample = 0;
        let index = 0;
        let deleting = false;
        const type = () => {
            const current = samples[sample];
            index = deleting ? index - 1 : index + 1;
            codeTarget.textContent = current.slice(0, index);
            if (!deleting && index === current.length) {
                deleting = true;
                setTimeout(type, 1400);
                return;
            }
            if (deleting && index === 0) {
                deleting = false;
                sample = (sample + 1) % samples.length;
            }
            setTimeout(type, deleting ? 18 : 34);
        };
        setTimeout(type, 700);
    };
    const initAmbientCanvas = () => {
        const canvas = document.getElementById("heroCanvas");
        if (!canvas || !enableAmbientCanvas) {
            return;
        }
        const ctx = canvas.getContext("2d");
        if (!ctx) {
            return;
        }
        const pointer = { x: -9999, y: -9999 };
        const points = [];
        let viewportWidth = 0;
        let viewportHeight = 0;
        const resize = () => {
            viewportWidth = window.innerWidth;
            viewportHeight = window.innerHeight;
            const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
            canvas.width = viewportWidth * ratio;
            canvas.height = viewportHeight * ratio;
            canvas.style.width = `${viewportWidth}px`;
            canvas.style.height = `${viewportHeight}px`;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            points.length = 0;
            const count = Math.min(Math.round(viewportWidth / 56), 18);
            for (let i = 0; i < count; i += 1) {
                points.push({
                    x: Math.random() * viewportWidth,
                    y: Math.random() * viewportHeight,
                    vx: (Math.random() - 0.5) * 0.22,
                    vy: (Math.random() - 0.5) * 0.22,
                });
            }
        };
        const render = () => {
            ctx.clearRect(0, 0, viewportWidth, viewportHeight);
            for (let i = 0; i < points.length; i += 1) {
                const point = points[i];
                point.x += point.vx;
                point.y += point.vy;
                if (point.x < 0 || point.x > viewportWidth) {
                    point.vx *= -1;
                }
                if (point.y < 0 || point.y > viewportHeight) {
                    point.vy *= -1;
                }
                for (let j = i + 1; j < points.length; j += 1) {
                    const other = points[j];
                    const distance = Math.hypot(point.x - other.x, point.y - other.y);
                    if (distance < 124) {
                        ctx.strokeStyle = `rgba(124, 231, 214, ${0.11 - distance / 1500})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(point.x, point.y);
                        ctx.lineTo(other.x, other.y);
                        ctx.stroke();
                    }
                }
                const distanceToPointer = Math.hypot(point.x - pointer.x, point.y - pointer.y);
                ctx.fillStyle = distanceToPointer < 140 ? "rgba(198,242,123,.82)" : "rgba(124,231,214,.64)";
                ctx.beginPath();
                ctx.arc(point.x, point.y, distanceToPointer < 140 ? 2.8 : 2, 0, Math.PI * 2);
                ctx.fill();
            }
            requestAnimationFrame(render);
        };
        window.addEventListener("mousemove", (event) => {
            pointer.x = event.clientX;
            pointer.y = event.clientY;
        }, { passive: true });
        window.addEventListener("resize", resize, { passive: true });
        resize();
        render();
    };
    const initFlashDismiss = () => {
        setTimeout(() => {
            document.querySelectorAll(".flash").forEach((flash) => {
                flash.style.opacity = "0";
                flash.style.transform = "translateY(-6px)";
                setTimeout(() => flash.remove(), 220);
            });
        }, 3400);
    };
    window.addEventListener("load", () => {
        runWhenIdle(initNavTracking);
        runWhenIdle(initScrollProgress);
        runWhenIdle(initSectionAnimations);
        runWhenIdle(initPointerEffects);
        runWhenIdle(initCounters);
        runWhenIdle(initTypewriter);
        runWhenIdle(initAmbientCanvas);
        runWhenIdle(initFlashDismiss);
    }, { once: true });
})();
