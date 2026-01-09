import { Download, GitBranch } from "lucide-react"
import { Button } from "@/components/ui/button"

export function CTA() {
  return (
    <section className="py-24 md:py-32 bg-emerald-600 text-white relative overflow-hidden">
      <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYwMCI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMSIvPjwvZz48L2c+PC9zdmc+')] opacity-30" />

      <div className="container mx-auto max-w-6xl px-4 relative">
        <div className="text-center max-w-4xl mx-auto">
          <h2 className="text-4xl md:text-6xl font-black mb-6 tracking-tight">
            Ready to Blitz Your WordPress?
          </h2>
          <p className="text-xl md:text-2xl mb-10 opacity-95 max-w-3xl mx-auto leading-relaxed">
            Join thousands of developers using Blitz Cache for lightning-fast WordPress performance.
            Install in seconds, cache instantly.
          </p>

          <div className="flex flex-col sm:flex-row gap-5 justify-center items-center mb-12">
            <Button size="lg" className="gap-3 bg-white text-emerald-600 hover:bg-gray-100 border-0 font-bold px-8 py-6 text-lg shadow-xl hover:shadow-2xl transition-all">
              <a href="https://github.com/ersinkoc/blitz-cache/releases" className="flex items-center gap-3">
                <Download className="h-6 w-6" />
                Download Free
              </a>
            </Button>
            <Button size="lg" variant="outline" className="gap-3 bg-transparent border-2 border-white text-white hover:bg-white hover:text-emerald-600 font-bold px-8 py-6 text-lg transition-all">
              <a href="https://github.com/ersinkoc/blitz-cache" className="flex items-center gap-3">
                <GitBranch className="h-6 w-6" />
                View on GitHub
              </a>
            </Button>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-black mb-2">100%</div>
              <div className="text-sm opacity-90">Free Forever</div>
            </div>
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-black mb-2">0</div>
              <div className="text-sm opacity-90">Configuration</div>
            </div>
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-black mb-2">∞</div>
              <div className="text-sm opacity-90">Open Source</div>
            </div>
            <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-black mb-2">24/7</div>
              <div className="text-sm opacity-90">Active Support</div>
            </div>
          </div>

          <div className="mt-12 pt-8 border-t border-white/20">
            <p className="text-white/80">
              <a href="https://github.com/ersinkoc/blitz-cache/blob/main/docs/installation.md" className="underline hover:text-white transition-colors font-medium">
                Installation Guide
              </a>
              <span className="mx-3">•</span>
              <a href="https://github.com/ersinkoc/blitz-cache/issues" className="underline hover:text-white transition-colors font-medium">
                Report Issues
              </a>
            </p>
          </div>
        </div>
      </div>
    </section>
  )
}
