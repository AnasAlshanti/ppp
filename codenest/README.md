# CodeNest — Hero Section

A high-end, dark-themed hero section for the **CodeNest** coding education
platform, built with **React + Vite + Tailwind CSS**.

## Features

- **HLS background video** via `hls.js` (`enableWorker: false` for sandbox
  stability) with a native-HLS fallback for Safari. Video is rendered at 60%
  opacity.
- **Readability overlays** — a left→transparent dark gradient and a
  bottom→top gradient.
- **Grid system** — three thin vertical lines at 25% / 50% / 75% (desktop).
- **Central glow** — a large horizontal SVG ellipse with a 25px Gaussian blur,
  in a cyan / dark-green hue.
- **Liquid glass card** — a 200×200 frosted card with a razor-thin gradient
  border frame built using `mask-composite`, lifted `-50px` over the headline.
- **Typography** — Inter, Plus Jakarta Sans, and Instrument Serif (italic).
- **Responsive navigation** — desktop menu plus a functional full-screen mobile
  hamburger overlay.
- Icons from `lucide-react` (`ArrowRight`, `Menu`, `X`).

## Getting started

```bash
cd codenest
npm install
npm run dev      # start the dev server (http://localhost:5173)
npm run build    # production build into dist/
npm run preview  # preview the production build
```

## Project structure

```
codenest/
├── index.html
├── tailwind.config.js
├── postcss.config.js
├── vite.config.js
└── src/
    ├── main.jsx
    ├── App.jsx
    ├── index.css                 # fonts, Tailwind layers, .liquid-glass
    └── components/
        ├── Hero.jsx              # layout: video, overlays, glow, grid, content
        ├── Navbar.jsx            # desktop links + mobile overlay
        ├── BackgroundVideo.jsx   # hls.js streaming
        └── LiquidGlassCard.jsx   # 200x200 frosted card
```
