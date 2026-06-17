import { ArrowRight } from 'lucide-react'
import Navbar from './Navbar.jsx'
import BackgroundVideo from './BackgroundVideo.jsx'
import LiquidGlassCard from './LiquidGlassCard.jsx'

/** Three thin vertical grid lines at 25% / 50% / 75% (desktop only). */
function GridLines() {
  return (
    <div
      className="pointer-events-none absolute inset-0 z-10 hidden lg:block"
      aria-hidden="true"
    >
      {['left-1/4', 'left-1/2', 'left-3/4'].map((pos) => (
        <div
          key={pos}
          className={`absolute top-0 ${pos} h-full w-px bg-white/10`}
        />
      ))}
    </div>
  )
}

/** Large horizontal ellipse glow (cyan / dark-green) with a 25px Gaussian blur. */
function CenterGlow() {
  return (
    <svg
      className="pointer-events-none absolute left-1/2 top-[-160px] z-10 -translate-x-1/2"
      width="1100"
      height="620"
      viewBox="0 0 1100 620"
      fill="none"
      aria-hidden="true"
    >
      <defs>
        <filter
          id="glow-blur"
          x="-50%"
          y="-50%"
          width="200%"
          height="200%"
          filterUnits="objectBoundingBox"
        >
          <feGaussianBlur stdDeviation="25" />
        </filter>
        <radialGradient id="glow-fill" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stopColor="#36e0b0" stopOpacity="0.55" />
          <stop offset="55%" stopColor="#108b76" stopOpacity="0.30" />
          <stop offset="100%" stopColor="#070b0a" stopOpacity="0" />
        </radialGradient>
      </defs>
      <ellipse
        cx="550"
        cy="310"
        rx="430"
        ry="180"
        fill="url(#glow-fill)"
        filter="url(#glow-blur)"
      />
    </svg>
  )
}

export default function Hero() {
  return (
    <section className="relative min-h-screen w-full overflow-hidden bg-ink">
      {/* Background video */}
      <BackgroundVideo />

      {/* Readability overlays */}
      {/* Left -> transparent dark gradient */}
      <div
        className="pointer-events-none absolute inset-0 z-[1]"
        style={{
          background:
            'linear-gradient(90deg, #070b0a 0%, rgba(7,11,10,0.55) 38%, rgba(7,11,10,0) 75%)',
        }}
        aria-hidden="true"
      />
      {/* Bottom -> top gradient */}
      <div
        className="pointer-events-none absolute inset-0 z-[1]"
        style={{
          background:
            'linear-gradient(0deg, #070b0a 0%, rgba(7,11,10,0.4) 30%, rgba(7,11,10,0) 65%)',
        }}
        aria-hidden="true"
      />

      {/* Central glow + grid system */}
      <CenterGlow />
      <GridLines />

      {/* Navigation */}
      <Navbar />

      {/* Hero content */}
      <div className="relative z-20 mx-auto flex min-h-screen max-w-7xl flex-col items-center justify-center px-6 text-center">
        {/* Floating liquid glass card (sits above the headline) */}
        <LiquidGlassCard />

        {/* Eyebrow */}
        <p className="animate-fade-up font-jakarta text-[11px] font-bold uppercase tracking-[0.25em] text-mint">
          Career-Ready Curriculum
        </p>

        {/* Main headline */}
        <h1
          className="animate-fade-up mt-5 max-w-4xl font-inter text-[40px] font-extrabold uppercase leading-[1.02] tracking-tight text-white sm:text-[56px] lg:text-[72px]"
          style={{ animationDelay: '90ms' }}
        >
          Launch Your Coding Career<span className="text-mint">.</span>
        </h1>

        {/* Description */}
        <p
          className="animate-fade-up mt-6 max-w-prose512 font-inter text-[14px] leading-relaxed text-white/70"
          style={{ animationDelay: '180ms' }}
        >
          Master in-demand coding skills through project-based learning, expert
          mentorship, and a curriculum built around what employers actually
          hire for.
        </p>

        {/* Primary CTA */}
        <div
          className="animate-fade-up mt-9"
          style={{ animationDelay: '260ms' }}
        >
          <a
            href="#"
            className="group inline-flex items-center gap-2 rounded-full bg-mint px-8 py-4 font-inter text-[14px] font-bold uppercase tracking-wide text-ink shadow-[0_8px_30px_rgba(94,210,156,0.35)] transition-transform duration-200 hover:scale-[1.03] focus:outline-none focus-visible:ring-2 focus-visible:ring-mint/60 focus-visible:ring-offset-2 focus-visible:ring-offset-ink"
          >
            Get Started
            <ArrowRight
              size={18}
              className="transition-transform duration-200 group-hover:translate-x-1"
            />
          </a>
        </div>
      </div>
    </section>
  )
}
