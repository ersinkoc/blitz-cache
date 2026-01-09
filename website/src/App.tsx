import { ThemeProvider } from "@/hooks/useTheme"
import { Navigation } from "@/components/Navigation"
import { Hero } from "@/components/Hero"
import { Features } from "@/components/Features"
import { Pricing } from "@/components/Pricing"
import { Testimonials } from "@/components/Testimonials"
import { FAQ } from "@/components/FAQ"
import { CTA } from "@/components/CTA"
import { Footer } from "@/components/Footer"

function App() {
  return (
    <ThemeProvider defaultTheme="system" storageKey="blitz-cache-theme">
      <div className="min-h-screen bg-background font-sans antialiased">
        <Navigation />
        <main>
          <Hero />
          <Features />
          <Pricing />
          <Testimonials />
          <FAQ />
          <CTA />
        </main>
        <Footer />
      </div>
    </ThemeProvider>
  )
}

export default App
