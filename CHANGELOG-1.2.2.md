# Nera Competitions Standard 1.2.2

## Instant Wins — group display mode

- **PrizeGroupCard.vue:** new collapsible group cards with remaining/won badges and winner grid when the REST payload uses `display_mode=group` (individual ticket numbers are not shown in the UI).
- **InstantWinsContainer.vue:** reads `display_mode` from the API; renders `PrizeGroupCard` in group mode and existing `PrizeCard` in default mode; normalizes `tickets[]` on group entries.
