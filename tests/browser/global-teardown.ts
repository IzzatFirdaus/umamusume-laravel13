import { assertBuildSnapshot, removeScratchPointer } from './global-setup';

/**
 * Run-scoped cleanup. Only the pointer that carries this run's id may be removed — a peer session's
 * pointer, database and WAL files stay untouched. The scratch database itself is intentionally left
 * on disk (git-ignored evidence); the next run with the same explicit path replaces it at setup.
 *
 * The build assertion turns a concurrent asset swap into a failed run instead of a green report
 * about somebody else's bundle. It does not alter the mandatory fresh-build check in setup.
 */
export default async function globalTeardown(): Promise<void> {
    assertBuildSnapshot();
    removeScratchPointer();
}
