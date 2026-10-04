// Inertia shared props, declared once so pages and the layout read them typed
// (ADR-0020 §1). Shape mirrors HandleInertiaRequests::share().
declare module '@inertiajs/core' {
    interface PageProps {
        app: {
            name: string;
            version: string | null;
            ruleset: string | null;
        };
    }
}

export {};
