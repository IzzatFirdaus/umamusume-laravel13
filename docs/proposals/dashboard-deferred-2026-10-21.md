# Dashboard deferrals (2026-10-21)

Three proposed Dashboard changes are deferred to the operator. No code change ships for any of them.

## #8 Quick Actions: keep "Open settings"

The proposal trims the Quick Actions card from four destinations to three by dropping "Open settings". The sidebar collapses below `md`, and on mobile the Settings destination sits behind the bottom bar's More disclosure. The "Open settings" Quick Action is the direct, one-tap path that does not require opening the disclosure first. Dropping it removes that direct path, so the trim is the operator's call, not a cleanup.

## #11 Border token: leave `border-rule` as signed

The proposal changes the border colour token (it names `#6e6c73` to `#34343d`). `DESIGN.md` is the signed visual system, and a colour-token edit is exactly what the arc has refused without a Track 2 amendment. The base token is not editable here. If the border reads too loud against muted copy, the fix is a lower-contrast class on the affected surface (for example `border-rule/60`, if the token system supports opacity), never a base-token change.

## #12 Sidebar grouping: leave the shell alone

The proposal groups the sidebar into Training / Library / System. That reorders `AppLayout.vue`'s ten-destination navigation, the load-bearing shell every page inherits. It is a shell restructure, not a Dashboard fix, and it is deferred.