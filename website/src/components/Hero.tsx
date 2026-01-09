import { Download, GitBranch } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"

export function Hero() {
  return (
    <section className="relative bg-background py-24 md:py-32">
      <div className="container relative mx-auto max-w-7xl px-4">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          <div className="space-y-8 animate-fade-in">
            <Badge variant="success" className="w-fit">
              🚀 Version 1.0.0 Released
            </Badge>

            <div className="space-y-6">
              <h1 className="text-5xl md:text-6xl lg:text-7xl font-black tracking-tight text-foreground">
                <span className="block">Blitz</span>
                <span className="block bg-gradient-to-r from-emerald-500 to-cyan-500 bg-clip-text text-transparent">
                  Cache
                </span>
              </h1>

              <p className="text-xl md:text-2xl text-muted-foreground max-w-2xl">
                High-performance page caching for WordPress.
                File-based cache, automatic purging, minification, and Cloudflare sync.
              </p>
            </div>

            <div className="flex flex-col sm:flex-row gap-4">
              <Button size="lg" className="gap-2 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white border-0">
                <a href="https://github.com/ersinkoc/blitz-cache/releases" className="flex items-center gap-2">
                  <Download className="h-5 w-5" />
                  Download Now
                </a>
              </Button>
              <Button size="lg" variant="outline" className="gap-2 border-2">
                <a href="https://github.com/ersinkoc/blitz-cache" className="flex items-center gap-2">
                  <GitBranch className="h-5 w-5" />
                  View on GitHub
                </a>
              </Button>
            </div>

            <div className="flex flex-wrap gap-6 pt-4 text-sm">
              <div className="flex items-center gap-2 text-emerald-600">
                <span className="h-2 w-2 rounded-full bg-current" />
                <span className="font-medium">100% Free</span>
              </div>
              <div className="flex items-center gap-2 text-cyan-600">
                <span className="h-2 w-2 rounded-full bg-current" />
                <span className="font-medium">Open Source</span>
              </div>
              <div className="flex items-center gap-2 text-blue-600">
                <span className="h-2 w-2 rounded-full bg-current" />
                <span className="font-medium">WordPress 6.0+</span>
              </div>
            </div>
          </div>

          <div className="relative lg:ml-10">
            <div className="absolute -inset-6 bg-gradient-to-r from-emerald-500/30 via-cyan-500/30 to-blue-500/30 rounded-3xl blur-3xl" />
            <div className="relative rounded-3xl border border-border/50 bg-card/95 backdrop-blur-sm p-8 shadow-2xl">
              <div className="space-y-8">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className="h-14 w-14 rounded-2xl bg-gradient-to-r from-emerald-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                      <span className="text-white font-black text-2xl">⚡</span>
                    </div>
                    <div>
                      <div className="font-black text-2xl bg-gradient-to-r from-emerald-600 to-cyan-600 bg-clip-text text-transparent">Blitz Cache</div>
                      <div className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                        Active & Running
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-sm text-muted-foreground">Performance Score</div>
                    <div className="text-3xl font-black text-emerald-600">98.5</div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="rounded-2xl bg-gradient-to-br from-emerald-500/15 to-emerald-600/5 p-5 border border-emerald-500/20 hover:border-emerald-500/40 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-emerald-700 dark:text-emerald-400">Cache Hits</span>
                      <span className="text-2xl">🎯</span>
                    </div>
                    <div className="text-3xl font-black text-emerald-600">98.5%</div>
                    <div className="mt-2 h-1.5 rounded-full bg-emerald-500/20">
                      <div className="h-full w-[98.5%] rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600" />
                    </div>
                  </div>

                  <div className="rounded-2xl bg-gradient-to-br from-cyan-500/15 to-cyan-600/5 p-5 border border-cyan-500/20 hover:border-cyan-500/40 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-cyan-700 dark:text-cyan-400">Load Time</span>
                      <span className="text-2xl">⚡</span>
                    </div>
                    <div className="text-3xl font-black text-cyan-600">0.12s</div>
                    <div className="mt-2 h-1.5 rounded-full bg-cyan-500/20">
                      <div className="h-full w-[95%] rounded-full bg-gradient-to-r from-cyan-500 to-cyan-600" />
                    </div>
                  </div>

                  <div className="rounded-2xl bg-gradient-to-br from-blue-500/15 to-blue-600/5 p-5 border border-blue-500/20 hover:border-blue-500/40 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-blue-700 dark:text-blue-400">Cache Size</span>
                      <span className="text-2xl">💾</span>
                    </div>
                    <div className="text-3xl font-black text-blue-600">245 MB</div>
                    <div className="mt-2 h-1.5 rounded-full bg-blue-500/20">
                      <div className="h-full w-[75%] rounded-full bg-gradient-to-r from-blue-500 to-blue-600" />
                    </div>
                  </div>

                  <div className="rounded-2xl bg-gradient-to-br from-indigo-500/15 to-indigo-600/5 p-5 border border-indigo-500/20 hover:border-indigo-500/40 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-indigo-700 dark:text-indigo-400">Pages Cached</span>
                      <span className="text-2xl">📄</span>
                    </div>
                    <div className="text-3xl font-black text-indigo-600">1,247</div>
                    <div className="mt-2 h-1.5 rounded-full bg-indigo-500/20">
                      <div className="h-full w-[85%] rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600" />
                    </div>
                  </div>
                </div>

                <div className="rounded-2xl bg-gradient-to-r from-muted/50 to-muted/30 p-6 border border-border/50">
                  <div className="flex items-center gap-3 mb-4">
                    <div className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                    <span className="text-sm font-medium">System Status</span>
                  </div>
                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        Cache Status
                      </span>
                      <span className="text-sm font-bold text-emerald-600">● Active</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-cyan-500" />
                        GZIP Compression
                      </span>
                      <span className="text-sm font-bold text-cyan-600">✓ Enabled</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-blue-500" />
                        Auto Purge
                      </span>
                      <span className="text-sm font-bold text-blue-600">On Change</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-indigo-500" />
                        Cloudflare Sync
                      </span>
                      <span className="text-sm font-bold text-indigo-600">Connected</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
