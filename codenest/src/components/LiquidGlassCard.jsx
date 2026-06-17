/**
 * 200x200 "liquid glass" floating card that sits above the main headline.
 * The frosted surface + gradient border frame live in the `.liquid-glass`
 * class (see index.css); this component owns the layout and copy.
 */
export default function LiquidGlassCard() {
  return (
    <div className="flex justify-center">
      <div
        className="liquid-glass flex h-[200px] w-[200px] -translate-y-[50px] flex-col justify-between p-5 text-left animate-fade-in"
        style={{ animationDelay: '120ms' }}
      >
        {/* Year tag */}
        <span className="text-[14px] font-medium tracking-[0.18em] text-white/70">
          [ 2025 ]
        </span>

        {/* Headline — "Industry" set in Instrument Serif italic */}
        <h3 className="text-[18px] font-semibold leading-snug text-white">
          Taught by{' '}
          <span className="font-instrument italic font-normal text-mint">
            Industry
          </span>{' '}
          Professionals
        </h3>

        {/* Supporting description */}
        <p className="text-[11px] leading-relaxed text-white/55">
          Learn directly from engineers shipping real products at top companies.
        </p>
      </div>
    </div>
  )
}
