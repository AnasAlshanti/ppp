import { useEffect, useState } from 'react'
import { Menu, X } from 'lucide-react'

const LINKS = ['PROJECTS', 'BLOG', 'ABOUT', 'RESUME']

/** Minimalist wordmark logo (white). */
function Logo({ className = '' }) {
  return (
    <a
      href="#"
      className={`group flex items-center gap-2 ${className}`}
      aria-label="CodeNest home"
    >
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill="none"
        className="text-white"
        aria-hidden="true"
      >
        <path
          d="M8.5 7.5 4 12l4.5 4.5M15.5 7.5 20 12l-4.5 4.5"
          stroke="currentColor"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      </svg>
      <span className="text-[18px] font-extrabold uppercase tracking-tight text-white">
        Code<span className="text-mint">Nest</span>
      </span>
    </a>
  )
}

export default function Navbar() {
  const [open, setOpen] = useState(false)

  // Lock body scroll while the mobile overlay is open.
  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [open])

  // Close the overlay on Escape.
  useEffect(() => {
    const onKey = (e) => e.key === 'Escape' && setOpen(false)
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [])

  return (
    <header className="absolute inset-x-0 top-0 z-50">
      <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
        <Logo />

        {/* Desktop menu */}
        <ul className="hidden items-center gap-10 lg:flex">
          {LINKS.map((link) => (
            <li key={link}>
              <a
                href="#"
                className="text-[16px] font-medium tracking-wide text-white/80 transition-colors duration-200 hover:text-mint"
              >
                {link}
              </a>
            </li>
          ))}
        </ul>

        {/* Mobile hamburger */}
        <button
          type="button"
          onClick={() => setOpen(true)}
          className="inline-flex items-center justify-center rounded-md p-2 text-white transition-colors hover:text-mint lg:hidden"
          aria-label="Open menu"
          aria-expanded={open}
        >
          <Menu size={26} />
        </button>
      </nav>

      {/* Full-screen mobile overlay */}
      <div
        className={`fixed inset-0 z-50 bg-ink/95 backdrop-blur-md transition-opacity duration-300 lg:hidden ${
          open ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'
        }`}
        role="dialog"
        aria-modal="true"
        aria-hidden={!open}
      >
        <div className="flex items-center justify-between px-6 py-6">
          <Logo />
          <button
            type="button"
            onClick={() => setOpen(false)}
            className="inline-flex items-center justify-center rounded-md p-2 text-white transition-colors hover:text-mint"
            aria-label="Close menu"
          >
            <X size={28} />
          </button>
        </div>

        <ul className="flex flex-col items-center justify-center gap-8 pt-[18vh]">
          {LINKS.map((link, i) => (
            <li
              key={link}
              className={open ? 'animate-fade-up' : ''}
              style={{ animationDelay: `${i * 70}ms` }}
            >
              <a
                href="#"
                onClick={() => setOpen(false)}
                className="text-3xl font-extrabold uppercase tracking-tight text-white transition-colors hover:text-mint"
              >
                {link}
              </a>
            </li>
          ))}
        </ul>
      </div>
    </header>
  )
}
