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
                <span className="block text-emerald-600">
                  Cache
                </span>
              </h1>

              <p className="text-xl md:text-2xl text-muted-foreground max-w-2xl">
                High-performance page caching for WordPress.
                File-based cache, automatic purging, minification, and Cloudflare sync.
              </p>
            </div>

            <div className="flex flex-col sm:flex-row gap-4">
              <Button size="lg" className="gap-2 bg-emerald-600 hover:bg-emerald-700 text-white border-0">
                <a href="https://github.com/BlitzCache/blitzcache/releases" className="flex items-center gap-2">
                  <Download className="h-5 w-5" />
                  Download Now
                </a>
              </Button>
              <Button size="lg" variant="outline" className="gap-2 border-2">
                <a href="https://github.com/BlitzCache/blitzcache" className="flex items-center gap-2">
                  <GitBranch className="h-5 w-5" />
                  View on GitHub
                </a>
              </Button>
            </div>

            <div className="flex flex-wrap gap-6 pt-4 text-sm">
              <div className="flex items-center gap-2 text-emerald-600">
                <span className="h-2 w-2 rounded-full bg-emerald-600" />
                <span className="font-medium">100% Free</span>
              </div>
              <div className="flex items-center gap-2 text-slate-600">
                <span className="h-2 w-2 rounded-full bg-slate-600" />
                <span className="font-medium">Open Source</span>
              </div>
              <div className="flex items-center gap-2 text-blue-600">
                <span className="h-2 w-2 rounded-full bg-blue-600" />
                <span className="font-medium">WordPress 6.0+</span>
              </div>
            </div>
          </div>

          <div className="relative lg:ml-10">
            <div className="absolute -inset-6 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYwMCI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMSIvPjwvZz48L2c+PC9zdmc+')] opacity-40" />
            <div className="relative rounded-3xl border border-border bg-card p-8 shadow-xl">
              <div className="space-y-8">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className="h-14 w-14 rounded-2xl bg-emerald-600 flex items-center justify-center shadow-lg">
                      <span className="text-white font-black text-2xl">⚡</span>
                    </div>
                    <div>
                      <div className="font-black text-2xl text-emerald-600">Blitz Cache</div>
                      <div className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-2 w-2 rounded-full bg-emerald-600" />
                        Active & Running
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-sm text-muted-foreground">Performance</div>
                    <div className="text-3xl font-black text-emerald-600">98.5</div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="rounded-2xl bg-emerald-50 dark:bg-emerald-950/20 p-5 border border-emerald-200 dark:border-emerald-800/50 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-emerald-700 dark:text-emerald-400">Cache Hits</span>
                      <span className="text-xl">🎯</span>
                    </div>
                    <div className="text-3xl font-black text-emerald-600">98.5%</div>
                    <div className="mt-3 h-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full overflow-hidden">
                      <div className="h-full w-[98.5%] bg-emerald-600" />
                    </div>
                  </div>

                  <div className="rounded-2xl bg-slate-50 dark:bg-slate-950/20 p-5 border border-slate-200 dark:border-slate-800/50 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-slate-700 dark:text-slate-400">Load Time</span>
                      <span className="text-xl">⚡</span>
                    </div>
                    <div className="text-3xl font-black text-slate-700 dark:text-slate-300">0.12s</div>
                    <div className="mt-3 h-2 bg-slate-100 dark:bg-slate-900/50 rounded-full overflow-hidden">
                      <div className="h-full w-[95%] bg-slate-600" />
                    </div>
                  </div>

                  <div className="rounded-2xl bg-blue-50 dark:bg-blue-950/20 p-5 border border-blue-200 dark:border-blue-800/50 hover:border-blue-300 dark:hover:border-blue-700 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-blue-700 dark:text-blue-400">Cache Size</span>
                      <span className="text-xl">💾</span>
                    </div>
                    <div className="text-3xl font-black text-blue-600">245 MB</div>
                    <div className="mt-3 h-2 bg-blue-100 dark:bg-blue-900/50 rounded-full overflow-hidden">
                      <div className="h-full w-[75%] bg-blue-600" />
                    </div>
                  </div>

                  <div className="rounded-2xl bg-purple-50 dark:bg-purple-950/20 p-5 border border-purple-200 dark:border-purple-800/50 hover:border-purple-300 dark:hover:border-purple-700 transition-all">
                    <div className="flex items-center justify-between mb-3">
                      <span className="text-sm font-medium text-purple-700 dark:text-purple-400">Pages Cached</span>
                      <span className="text-xl">📄</span>
                    </div>
                    <div className="text-3xl font-black text-purple-600">1,247</div>
                    <div className="mt-3 h-2 bg-purple-100 dark:bg-purple-900/50 rounded-full overflow-hidden">
                      <div className="h-full w-[85%] bg-purple-600" />
                    </div>
                  </div>
                </div>

                <div className="rounded-2xl bg-slate-50 dark:bg-slate-900/30 p-6 border border-slate-200 dark:border-slate-800">
                  <div className="flex items-center gap-3 mb-4">
                    <span className="h-2 w-2 rounded-full bg-emerald-600" />
                    <span className="text-sm font-medium">System Status</span>
                  </div>
                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-600" />
                        Cache Status
                      </span>
                      <span className="text-sm font-bold text-emerald-600">● Active</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-600" />
                        GZIP Compression
                      </span>
                      <span className="text-sm font-bold text-slate-600">✓ Enabled</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-blue-600" />
                        Auto Purge
                      </span>
                      <span className="text-sm font-bold text-blue-600">On Change</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-muted-foreground flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-purple-600" />
                        Cloudflare Sync
                      </span>
                      <span className="text-sm font-bold text-purple-600">Connected</span>
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
