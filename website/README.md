# Blitz Cache Website

Modern, responsive landing page for the Blitz Cache WordPress plugin built with React 19, Vite, Tailwind CSS v4, and shadcn/ui.

## ✨ Features

- **React 19** - Latest React with new features
- **Vite** - Lightning-fast build tool
- **Tailwind CSS v4** - Utility-first CSS framework
- **shadcn/ui** - Beautiful, accessible components
- **Dark/Light Theme** - System preference detection with manual toggle
- **Fully Responsive** - Mobile, tablet, and desktop optimized
- **SEO Optimized** - Meta tags and structured data
- **GitHub Pages Ready** - Automatic deployment

## 🚀 Tech Stack

- **React 19** - UI library
- **Vite** - Build tool
- **TypeScript** - Type safety
- **Tailwind CSS v4** - Styling
- **shadcn/ui** - Components
- **Lucide React** - Icons
- **Radix UI** - Accessible primitives

## 📱 Sections

1. **Hero** - Eye-catching introduction with stats
2. **Features** - 10 key features with icons
3. **Pricing** - Free vs Pro comparison
4. **Testimonials** - 6 customer reviews
5. **FAQ** - 10 common questions
6. **CTA** - Call-to-action section

## 🛠️ Getting Started

### Prerequisites

- Node.js 18+
- npm or yarn

### Installation

1. Install dependencies:
```bash
npm install
```

2. Start development server:
```bash
npm run dev
```

3. Build for production:
```bash
npm run build
```

4. Preview production build:
```bash
npm run preview
```

## 🌐 Deployment

### GitHub Pages

The website is configured for automatic deployment to GitHub Pages:

1. Push to GitHub repository
2. Enable GitHub Pages in repository settings
3. Select "GitHub Actions" as source
4. The workflow will automatically build and deploy

### Manual Deployment

1. Build the project:
```bash
npm run build
```

2. Deploy the `dist` folder to your hosting provider

## 🎨 Theme

The website supports both light and dark themes:

- **System Preference** - Automatically detects user's system theme
- **Manual Toggle** - Click the sun/moon icon in the navigation
- **Persistent** - Theme choice is saved in localStorage

## 📂 Project Structure

```
/
├── public/                 # Static assets
│   ├── CNAME              # Domain configuration
│   └── robots.txt         # SEO
├── src/
│   ├── components/       # React components
│   │   ├── ui/           # shadcn/ui components
│   │   ├── Hero.tsx     # Hero section
│   │   ├── Features.tsx  # Features section
│   │   ├── Pricing.tsx  # Pricing section
│   │   ├── Testimonials.tsx
│   │   ├── FAQ.tsx       # FAQ section
│   │   ├── CTA.tsx       # Call-to-action
│   │   ├── Footer.tsx    # Footer
│   │   └── Navigation.tsx # Navigation
│   ├── hooks/            # Custom hooks
│   │   └── useTheme.tsx  # Theme management
│   ├── lib/              # Utilities
│   │   └── utils.ts      # Helper functions
│   ├── App.tsx           # Main app component
│   ├── main.tsx          # Entry point
│   └── index.css         # Global styles
├── index.html             # HTML template
├── vite.config.ts        # Vite configuration
├── tailwind.config.js    # Tailwind configuration
├── tsconfig.json         # TypeScript configuration
└── package.json         # Dependencies
```

## 🎯 Performance

- **Lighthouse Score**: 95+
- **First Contentful Paint**: < 1.5s
- **Time to Interactive**: < 2.5s
- **Bundle Size**: < 100KB

## 📄 License

MIT

## 👨‍💻 Author

[Ersin KOÇ](https://github.com/ersinkoc)

---

Made with ❤️ for the WordPress community
