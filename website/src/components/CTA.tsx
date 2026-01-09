import { ArrowRight, Download, Github } from "lucide-react"
import { Button } from "@/components/ui/button"

export function CTA() {
  return (
    <section className="py-20 md:py-32 bg-gradient-to-b from-purple-600 to-blue-600 text-white">
      <div className="container mx-auto max-w-4xl px-4 text-center">
        <h2 className="text-3xl md:text-5xl font-bold mb-6">
          Ready to Speed Up Your WordPress Site?
        </h2>
        <p className="text-lg md:text-xl mb-8 opacity-90 max-w-2xl mx-auto">
          Join thousands of developers who have transformed their WordPress performance with Blitz Cache.
          It's free, it's fast, and it works.
        </p>

        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
          <Button size="lg" variant="secondary" className="gap-2">
            <a href="https://github.com/ersinkoc/blitz-cache/releases">
              <Download className="h-5 w-5" />
              Download Blitz Cache
            </a>
          </Button>
          <Button size="lg" variant="outline" className="gap-2 bg-transparent border-white text-white hover:bg-white hover:text-purple-600">
            <a href="https://github.com/ersinkoc/blitz-cache">
              <Github className="h-5 w-5" />
              View on GitHub
            </a>
          </Button>
        </div>

        <div className="flex flex-wrap gap-6 justify-center items-center text-sm opacity-80">
          <div className="flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-white" />
            <span>100% Free Forever</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-white" />
            <span>Zero Configuration</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-white" />
            <span>Open Source</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-2 w-2 rounded-full bg-white" />
            <span>Active Development</span>
          </div>
        </div>

        <div className="mt-12 text-sm opacity-60">
          <p>
            Need help getting started?{" "}
            <a href="https://github.com/ersinkoc/blitz-cache/blob/main/docs/installation.md" className="underline hover:opacity-80">
              View our installation guide
            </a>
          </p>
        </div>
      </div>
    </section>
  )
}
