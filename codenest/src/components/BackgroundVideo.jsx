import { useEffect, useRef } from 'react'
import Hls from 'hls.js'

const HLS_SRC =
  'https://stream.mux.com/tLkHO1qZoaaQOUeVWo8hEBeGQfySP02EPS02BmnNFyXys.m3u8'

/**
 * Full-screen, autoplaying, muted, looping background video driven by an HLS
 * stream. hls.js is used where MSE is available (`enableWorker: false` keeps it
 * stable inside sandboxed iframes); Safari falls back to native HLS playback.
 */
export default function BackgroundVideo() {
  const videoRef = useRef(null)

  useEffect(() => {
    const video = videoRef.current
    if (!video) return

    let hls

    if (Hls.isSupported()) {
      hls = new Hls({
        enableWorker: false, // stability in sandboxed environments
        lowLatencyMode: false,
      })
      hls.loadSource(HLS_SRC)
      hls.attachMedia(video)
      hls.on(Hls.Events.MANIFEST_PARSED, () => {
        video.play().catch(() => {
          /* Autoplay can be blocked until user interaction — ignore. */
        })
      })
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      // Native HLS (Safari / iOS)
      video.src = HLS_SRC
      video.addEventListener(
        'loadedmetadata',
        () => {
          video.play().catch(() => {})
        },
        { once: true },
      )
    }

    return () => {
      if (hls) hls.destroy()
    }
  }, [])

  return (
    <video
      ref={videoRef}
      className="absolute inset-0 h-full w-full object-cover opacity-60"
      muted
      loop
      autoPlay
      playsInline
      preload="auto"
      aria-hidden="true"
      tabIndex={-1}
    />
  )
}
