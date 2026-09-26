---
name: tailwindcss-development
description: "Trigger when the user mentions 'tailwind' in any form, or when building responsive grid layouts, styling UI components, adding dark mode, or fixing spacing/typography in Blade templates or CSS files."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: frontend
---

# Tailwind CSS Development

Write and fix Tailwind CSS v4 styles in this project.

## Trigger Criteria

User message includes "tailwind", or the task involves: responsive grids, flex/grid layouts, component styling (cards, tables, navbars, forms, badges), dark mode, spacing, or typography.

## Setup

- **Tailwind version:** v4 via `@tailwindcss/vite`.
- **Entry point:** `resources/css/app.css`.
- **Vite plugin:** `@tailwindcss/vite` (configured in `resources/js/app.js`).

## Utility-First Approach

- Use Tailwind utility classes. Avoid custom CSS in component files unless a design token is missing.
- Extract repeated utility patterns into Blade components (`<x-card />`) or `@layer` component classes in `app.css`.

## Responsive Breakpoints

- Mobile-first: `sm:` (640px), `md:` (768px), `lg:` (1024px), `xl:` (1280px).

## Dark Mode

- Default: `prefers-color-scheme`. For manual toggle, add a custom variant:

<code-snippet name="dark-variant" lang="css">
@custom-variant dark (&:where(.dark, .dark *));
</code-snippet>

## State Variants

- `hover:`, `focus:`, `focus-within:`, `active:`, `disabled:`, `aria:`.

## Accessibility

- Minimum tap target: `min-h-11 min-w-11` on mobile.
- Focus-visible styling on custom interactive elements.
- Semantic HTML over `div` + `role`.

## Spacing & Typography

- Use the default Tailwind spacing scale. Avoid arbitrary values.
- Use `text-{size}` scale for typography; avoid `text-[13px]` unless necessary.