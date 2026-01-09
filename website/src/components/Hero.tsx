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
            <div className="absolute -inset-4 bg-gradient-to-r from-emerald-500/20 to-cyan-500/20 rounded-3xl blur-2xl" />
            <div className="relative rounded-2xl border-2 border-border bg-card p-8 shadow-2xl">
              <div className="space-y-6">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="h-10 w-10 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 flex items-center justify-center">
                      <span className="text-white font-bold text-lg">B</span>
                    </div>
                    <div>
                      <div className="font-bold text-lg">Blitz Cache</div>
                      <div className="text-xs text-muted-foreground">Active & Running</div>
                    </div>
                  </div>
                  <div className="h-3 w-3 rounded-full bg-emerald-500 animate-pulse" />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="rounded-xl bg-emerald-500/10 p-4 border border-emerald-500/20">
                    <div className="text-2xl font-bold text-emerald-600">98.5%</div>
                    <div className="text-xs text-muted-foreground">Cache Hit Rate</div>
                  </div>
                  <div className="rounded-xl bg-cyan-500/10 p-4 border border-cyan-500/20">
                    <div className="text-2xl font-bold text-cyan-600">0.12s</div>
                    <div className="text-xs text-muted-foreground">Avg Load Time</div>
                  </div>
                  <div className="rounded-xl bg-blue-500/10 p-4 border border-blue-500/20">
                    <div className="text-2xl font-bold text-blue-600">245 MB</div>
                    <div className="text-xs text-muted-foreground">Cache Size</div>
                  </div>
                  <div className="rounded-xl bg-purple-500/10 p-4 border border-purple-500/20">
                    <div className="text-2xl font-bold text-purple-600">1,247</div>
                    <div className="text-xs text-muted-foreground">Pages Cached</div>
                  </div>
                </div>

                <div className="rounded-lg bg-muted/50 p-4 font-mono text-sm space-y-2">
                  <div className="flex justify-between items-center">
                    <span className="text-muted-foreground">Status</span>
                    <span className="text-emerald-500 font-bold">● Active</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-muted-foreground">Gzip</span>
                    <span className="text-cyan-500 font-bold">Enabled</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-muted-foreground">Auto Purge</span>
                    <span className="text-blue-500 font-bold">On Change</span>
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
