---
name: tailwindcss-development
description: "Trigger when the user mentions 'tailwind' in any form, or when building responsive grid layouts, styling UI components, or fixing spacing/typography in Vue components or CSS files."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: frontend
---

# Tailwind CSS Development

Write and fix Tailwind CSS v4 styles in this project.

## Trigger Criteria

User message includes "tailwind", or the task involves: responsive grids, flex/grid layouts, component styling (cards, tables, navbars, forms, badges), spacing, or typography.

## Setup

- **Tailwind version:** v4 via `@tailwindcss/vite`.
- **Entry point:** `resources/css/app.css`.
- **Vite plugin:** `@tailwindcss/vite` (configured in `vite.config.ts`).
- **Markup:** Vue single-file components under `resources/js/pages/` and `resources/js/components/`; `resources/views/` holds only the Inertia shell and error pages.

## Utility-First Approach

- Use design tokens (`bg-page`, `text-ink`, `border-rule`, ...) before raw utilities (`AGENTS.md` §13).
- Avoid custom CSS in component files unless a design token is missing.
- Extract repeated utility patterns into Vue components under `resources/js/components/` or `@layer` component classes in `app.css`. Do not add Blade view components; `resources/views/components/` is empty.

## Responsive Breakpoints

- Mobile-first: `sm:` (640px), `md:` (768px), `lg:` (1024px), `xl:` (1280px).

## Theme

- Light and dark palettes are token ramps gated on `html[data-theme='dark']`, resolved by the shell and `resources/js/composables/useTheme.ts`.
- Zero `dark:` utilities and no custom `dark` variant: a theme change edits the token ramps in `resources/css/app.css`, not component classes (`AGENTS.md` §13).

## State Variants

- `hover:`, `focus:`, `focus-within:`, `active:`, `disabled:`, `aria:`.

## Accessibility

- Minimum tap target: `min-h-11 min-w-11` on mobile.
- Focus-visible styling on custom interactive elements.
- Semantic HTML over `div` + `role`.

## Spacing & Typography

- Use the default Tailwind spacing scale. Avoid arbitrary values.
- Use `text-{size}` scale for typography; avoid `text-[13px]` unless necessary.
