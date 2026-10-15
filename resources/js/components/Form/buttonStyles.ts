export type ButtonVariant = 'primary' | 'secondary' | 'destructive' | 'ghost' | 'link';
export type ButtonSize = 'sm' | 'md' | 'lg';

// `shadow-none` is explicit, not implied: the one elevation in the system is `--shadow-overlay`,
// and it belongs to true overlays only (Track 2-B). A primary button must never look lifted.
const base =
    'inline-flex min-h-11 items-center justify-center rounded-md font-semibold shadow-none transition-colors duration-150 ease-out focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:opacity-60 disabled:cursor-not-allowed';

const variants: Record<ButtonVariant, string> = {
    // The one filled action on a screen: solid brand, white label, no shine. The border is the same
    // colour as the fill so a primary and a secondary sitting side by side keep the same box height.
    // The fill names the action role, which aliases the chrome pair, so the button no longer depends
    // on the top bar's token name.
    primary: 'border border-primary-action bg-primary-action px-4 text-on-primary-action hover:bg-primary-action/90',
    secondary: 'border border-rule bg-raised px-4 text-ink hover:bg-panel',
    destructive: 'border border-risk bg-raised px-4 text-risk hover:bg-panel',
    ghost: 'border-transparent bg-transparent px-4 text-ink hover:bg-sunken',
    link: 'border-transparent bg-transparent px-0 text-ink-strong underline hover:text-ink',
};

const sizes: Record<ButtonSize, string> = {
    sm: 'text-sm py-1.5',
    md: 'text-sm py-2',
    lg: 'text-base py-2.5',
};

export function buttonClasses(
    variant: ButtonVariant = 'primary',
    size: ButtonSize = 'md',
): string {
    return `${base} ${variants[variant]} ${sizes[size]}`;
}
