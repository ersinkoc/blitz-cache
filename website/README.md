# Blitz Cache Website

Modern, responsive landing page for the Blitz Cache WordPress plugin.

## Tech Stack

- **Next.js 14** - React framework
- **TypeScript** - Type safety
- **Tailwind CSS** - Utility-first CSS
- **shadcn/ui** - Reusable components
- **Lucide React** - Beautiful icons

## Getting Started

### Prerequisites

- Node.js 18+
- npm or yarn

### Installation

1. Install dependencies:
```bash
npm install
```

2. Run the development server:
```bash
npm run dev
```

3. Open [http://localhost:3000](http://localhost:3000) in your browser

### Build for Production

```bash
npm run build
npm start
```

## Project Structure

```
/
├── app/                  # Next.js app directory
│   ├── globals.css       # Global styles
│   ├── layout.tsx        # Root layout
│   └── page.tsx         # Home page
├── components/           # Reusable components
│   ├── ui/              # shadcn/ui components
│   ├── hero.tsx         # Hero section
│   ├── features.tsx     # Features section
│   ├── pricing.tsx     # Pricing section
│   ├── testimonials.tsx # Testimonials
│   ├── faq.tsx         # FAQ section
│   ├── footer.tsx      # Footer
│   └── navigation.tsx   # Navigation header
├── lib/                  # Utilities
│   └── utils.ts         # Helper functions
└── public/               # Static assets
    └── ...
```

## Features

- ✅ Responsive design
- ✅ Modern UI with shadcn/ui
- ✅ Dark/light mode support
- ✅ Smooth animations
- ✅ SEO optimized
- ✅ Fast performance
- ✅ Accessible components

## License

MIT
