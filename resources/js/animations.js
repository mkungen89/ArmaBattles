import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Animate a number counting up from 0 to the target value on scroll.
 */
export function animateCountUp(element, endValue, options = {}) {
    if (prefersReducedMotion || !element) return;

    const {
        duration = 1.5,
        decimals = 0,
        suffix = '',
        prefix = '',
    } = options;

    const obj = { val: 0 };

    ScrollTrigger.create({
        trigger: element,
        start: 'top 85%',
        once: true,
        onEnter: () => {
            gsap.to(obj, {
                val: endValue,
                duration,
                ease: 'power2.out',
                onUpdate: () => {
                    const formatted = obj.val.toLocaleString(undefined, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals,
                    });
                    element.textContent = prefix + formatted + suffix;
                },
            });
        },
    });
}

/**
 * Animate playtime display (e.g., "142h 30m") counting up.
 */
export function animateCountUpPlaytime(element, totalSeconds) {
    if (prefersReducedMotion || !element) return;

    const obj = { val: 0 };
    const targetHours = Math.floor(totalSeconds / 3600);
    const targetMinutes = Math.floor((totalSeconds % 3600) / 60);

    ScrollTrigger.create({
        trigger: element,
        start: 'top 85%',
        once: true,
        onEnter: () => {
            gsap.to(obj, {
                val: totalSeconds,
                duration: 1.5,
                ease: 'power2.out',
                onUpdate: () => {
                    const h = Math.floor(obj.val / 3600);
                    const m = Math.floor((obj.val % 3600) / 60);
                    element.textContent = h + 'h ' + m + 'm';
                },
            });
        },
    });
}

/**
 * Stagger fade+slide-up animation for card grids.
 */
export function animateStaggerIn(selector, options = {}) {
    if (prefersReducedMotion) return;

    const {
        y = 30,
        duration = 0.6,
        stagger = 0.08,
    } = options;

    const elements = document.querySelectorAll(selector);
    if (!elements.length) return;

    gsap.from(elements, {
        y,
        opacity: 0,
        duration,
        stagger,
        ease: 'power2.out',
        scrollTrigger: {
            trigger: elements[0],
            start: 'top 85%',
            once: true,
        },
    });
}

/**
 * Achievement unlock animation: scale bounce + green glow pulse.
 */
export function animateAchievementUnlock(element) {
    if (prefersReducedMotion || !element) return;

    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: element,
            start: 'top 85%',
            once: true,
        },
    });

    tl.from(element, {
        scale: 0.8,
        opacity: 0,
        duration: 0.5,
        ease: 'back.out(1.5)',
    })
    .to(element, {
        boxShadow: '0 0 30px rgba(34, 197, 94, 0.4), 0 0 60px rgba(34, 197, 94, 0.1)',
        duration: 0.4,
        ease: 'power2.in',
    })
    .to(element, {
        boxShadow: '0 0 0px rgba(34, 197, 94, 0)',
        duration: 0.6,
        ease: 'power2.out',
    });
}

/**
 * Subtle page entrance animation for main content.
 */
export function animatePageEntrance() {
    if (prefersReducedMotion) return;

    const main = document.querySelector('main');
    if (!main) return;

    gsap.from(main, {
        y: 12,
        opacity: 0,
        duration: 0.4,
        ease: 'power2.out',
    });
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    if (prefersReducedMotion) return;

    animatePageEntrance();

    // Count-up animations for stat numbers
    document.querySelectorAll('[data-countup]').forEach((el) => {
        const value = parseFloat(el.dataset.countup);
        if (isNaN(value)) return;
        const decimals = parseInt(el.dataset.countupDecimals || '0', 10);
        const suffix = el.dataset.countupSuffix || '';
        const prefix = el.dataset.countupPrefix || '';
        animateCountUp(el, value, { decimals, suffix, prefix });
    });

    // Playtime count-up
    document.querySelectorAll('[data-countup-playtime]').forEach((el) => {
        const seconds = parseFloat(el.dataset.countupPlaytime);
        if (isNaN(seconds)) return;
        animateCountUpPlaytime(el, seconds);
    });

    // Achievement unlock animations
    document.querySelectorAll('.achievement-earned').forEach((el) => {
        animateAchievementUnlock(el);
    });

    // Stagger-in for achievement cards
    animateStaggerIn('.achievement-card');
});
