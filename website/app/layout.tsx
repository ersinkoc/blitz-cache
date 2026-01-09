import type { Metadata } from "next"
import { Inter } from "next/font/google"
import "./globals.css"
import { Navigation } from "@/components/navigation"
import { Footer } from "@/components/footer"

const inter = Inter({ subsets: ["latin"] })

export const metadata: Metadata = {
  title: "Blitz Cache - Lightning-Fast WordPress Caching",
  description: "Zero-config WordPress caching with Cloudflare Edge integration. Lightning-fast page loads through intelligent file-based caching.",
  keywords: [
    "WordPress cache",
    "WordPress caching",
    "WordPress performance",
    "Cloudflare",
    "page cache",
    "browser cache",
    "GZIP",
    "minify"
  ],
  authors: [{ name: "Ersin KOÇ" }],
  openGraph: {
    title: "Blitz Cache - Lightning-Fast WordPress Caching",
    description: "Zero-config WordPress caching with Cloudflare Edge integration",
    type: "website",
    url: "https://blitzcache.com",
  },
  twitter: {
    card: "summary_large_image",
    title: "Blitz Cache - Lightning-Fast WordPress Caching",
    description: "Zero-config WordPress caching with Cloudflare Edge integration",
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <Navigation />
        <main>{children}</main>
        <Footer />
      </body>
    </html>
  )
}
