import { Download, GitBranch, Star } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"

export function Hero() {
  return (
    <section className="relative overflow-hidden bg-gradient-to-b from-purple-50 to-white dark:from-purple-950/50 dark:to-background py-20 md:py-32">
      <div className="absolute inset-0 bg-grid dark:bg-grid-white/[0.02] bg-[size:20px_20px]" />

      <div className="container relative mx-auto max-w-6xl px-4">
        <div className="text-center space-y-8 animate-fade-in">
          <Badge variant="success" className="mb-4">
            🚀 Version 1.0.0 Released
          </Badge>

          <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold tracking-tight text-foreground">
            File-Based
            <br />
            <span className="gradient-text">WordPress Caching</span>
          </h1>

          <p className="text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto">
            High-performance page caching for WordPress with automatic purging.
            Cache HTML pages, minify CSS/JS, enable gzip compression, and integrate with Cloudflare.
            Zero configuration required.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <Button size="lg" className="gap-2">
              <a href="https://github.com/ersinkoc/blitz-cache/releases">
                <Download className="h-5 w-5" />
                Download Now
              </a>
            </Button>
            <Button size="lg" variant="outline" className="gap-2">
              <a href="https://github.com/ersinkoc/blitz-cache">
                <GitBranch className="h-5 w-5" />
                View on GitHub
              </a>
            </Button>
          </div>

          <div className="flex items-center justify-center gap-1">
            {[...Array(5)].map((_, i) => (
              <Star key={i} className="h-5 w-5 fill-yellow-400 text-yellow-400" />
            ))}
            <span className="ml-2 text-sm font-medium text-foreground">4.9/5 from 1,000+ developers</span>
          </div>

          <div className="flex flex-wrap gap-4 justify-center items-center text-sm text-muted-foreground">
            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-green-500" />
              <span>100% Free</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-blue-500" />
              <span>Open Source</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-purple-500" />
              <span>WordPress 6.0+</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-orange-500" />
              <span>PHP 8.0+</span>
            </div>
          </div>
        </div>

        <div className="mt-16 md:mt-24">
          <div className="relative mx-auto max-w-5xl">
            <div className="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent rounded-2xl blur-3xl" />
            <div className="relative rounded-2xl border bg-card p-4 shadow-xl">
              <div className="space-y-4">
                <div className="flex items-center gap-2">
                  <div className="h-3 w-3 rounded-full bg-red-500" />
                  <div className="h-3 w-3 rounded-full bg-yellow-500" />
                  <div className="h-3 w-3 rounded-full bg-green-500" />
                  <div className="ml-4 text-xs text-muted-foreground">
                    blitz-cache/stats.php
                  </div>
                </div>
                <div className="bg-muted/50 rounded-lg p-8 font-mono text-sm">
                  <div className="space-y-2">
                    <div className="flex justify-between">
                      <span>Cache Status:</span>
                      <span className="text-green-600 font-bold">Active</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Cache Directory:</span>
                      <span className="text-blue-600 font-bold">/wp-content/cache/blitz-cache</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Gzip Compression:</span>
                      <span className="text-purple-600 font-bold">Enabled</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Auto Purge:</span>
                      <span className="text-orange-600 font-bold">On Content Change</span>
                    </div>
                    <div className="border-t my-4 pt-4">
                      <div className="flex justify-between">
                        <span>Minification:</span>
                        <span className="text-green-600 font-bold">CSS + JS</span>
                      </div>
                      <div className="text-xs text-muted-foreground mt-2">
                        Cloudflare integration available
                      </div>
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
