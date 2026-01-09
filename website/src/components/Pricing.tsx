import { Heart, Zap, Infinity } from "lucide-react"
import { Card, CardContent } from "@/components/ui/card"

export function Pricing() {
  return (
    <section id="pricing" className="py-20 md:py-32 bg-muted/30">
      <div className="container mx-auto max-w-6xl px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Why Is It <span className="text-emerald-600">Always Free</span>?
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Blitz Cache is free because we believe performance should be accessible to everyone.
          </p>
        </div>

        <div className="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          <Card className="border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-300 hover:shadow-lg">
            <CardContent className="pt-8 text-center">
              <div className="inline-flex h-16 w-16 items-center justify-center rounded-full bg-emerald-600 text-white mb-6">
                <Heart className="h-8 w-8" />
              </div>
              <h3 className="text-xl font-bold mb-4">Built by Developers, for Developers</h3>
              <p className="text-muted-foreground">
                Created by WordPress developers who understand the importance of performance.
                We use it ourselves every day.
              </p>
            </CardContent>
          </Card>

          <Card className="border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-300 hover:shadow-lg">
            <CardContent className="pt-8 text-center">
              <div className="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-900 text-white mb-6">
                <Zap className="h-8 w-8" />
              </div>
              <h3 className="text-xl font-bold mb-4">Open Source Commitment</h3>
              <p className="text-muted-foreground">
                Code is open, features are free, and will always stay that way.
                No paywalls, no premium tiers, no limits.
              </p>
            </CardContent>
          </Card>

          <Card className="border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-300 hover:shadow-lg">
            <CardContent className="pt-8 text-center">
              <div className="inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-white mb-6">
                <Infinity className="h-8 w-8" />
              </div>
              <h3 className="text-xl font-bold mb-4">Give Back to Community</h3>
              <p className="text-muted-foreground">
                The best way to give back to the WordPress community is to build tools
                that help everyone succeed.
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="mt-16 text-center">
          <Card className="max-w-4xl mx-auto border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
            <CardContent className="pt-8">
              <div className="flex items-center justify-center gap-2 mb-6">
                <span className="text-4xl">💚</span>
                <h3 className="text-2xl font-bold">100% Free Forever</h3>
              </div>
              <p className="text-lg text-muted-foreground mb-4">
                No hidden fees, no subscriptions, no limits. Just pure performance.
              </p>
              <p className="text-sm text-muted-foreground">
                Support development on{" "}
                <a
                  href="https://github.com/sponsors/ersinkoc"
                  className="text-emerald-600 hover:underline font-medium"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  GitHub Sponsors
                </a>
                {" "}if you love Blitz Cache
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  )
}
