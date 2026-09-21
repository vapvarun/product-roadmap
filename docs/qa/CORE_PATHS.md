# Product Roadmap — Core Paths (the 60–70%)

> Walked first, every cycle, as the named role. A defect on a core path outranks
> any card in the column.
>
> **Seeded from evidence:** what activation creates (setup wizard + demo import +
> `roadmap_votes` table), what the Settings screen shows first (voting / comments
> / suggestions / who-can-vote), what readme.txt leads with, and what the Basecamp
> Bugs columns actually cluster on (voting, settings-save, product filtering).

Last confirmed: **NOT YET CONFIRMED BY A HUMAN** — seeded 2026-09-21 by Claude.
Ranking below is evidence-derived; it needs one human pass before it is the oracle.

| # | Flow (owner's words) | Role | Surface | Why it is core (evidence) | Free/Pro |
|---|---|---|---|---|---|
| 1 | "I activate it and get a working roadmap without configuring anything" | admin | Setup wizard → demo import → `[roadmap]` page | Zero-config path; wizard is the first screen | Free |
| 2 | "Visitors see my board and it loads" | anonymous | `[roadmap]` shortcode → `GET /items` | The product itself | Free |
| 3 | "People vote on what they want next" | member-owner | Vote control → `POST /vote` | Headline feature in readme; `roadmap_votes` table exists for it | Free |
| 4 | "Logged-out visitors can vote too" | anonymous | Vote control | `allow_guest_voting` defaults **on** | Free |
| 5 | "Users suggest features and I approve them" | member-owner → admin | Suggest form → pending queue | `enable_suggestions` defaults on | Free |
| 6 | "I move an item to Completed and voters hear about it" | admin | Status change → `wp_mail` | 3 `wp_mail` sites; notifications are a named feature | Free |
| 7 | "I turn voting off and the vote buttons go away" | admin → anonymous | Settings → board | `filter_capabilities()` exists for exactly this | Free |
| 8 | "I set my product's colour and the board matches" | admin | Product settings modal → frontend accent | Whole 1.7.1 release turns on this | Free |
| 9 | "I comment on an item" | member-owner | Comment modal → `POST /comment` | `enable_comments` defaults on | Free |
| 10 | "I publish a changelog entry" | admin | Changelog CPT → `[roadmap_changelog]` | Second top-level admin menu | Free |
| 11 | "I run more than one product board" | admin | Products screen → per-product shortcode | Products menu is Pro-gated in free | Pro |
| 12 | "I see which items are most wanted" | admin | Analytics tab | Pro headline | Pro |

## Edge (walk after core)

| Flow | Role | Surface | Why it is edge |
|---|---|---|---|
| Filter presets | member-owner | `GET /filter-presets` | No UI calls it; REST-only |
| Webhooks | admin | Pro webhooks | Opt-in integration |
| Export | admin | Pro export | Occasional, not daily |
| Merge duplicates | admin | Pro merge | Only on busy boards |

## Zero-config check (C-2)

**Activate the plugin, finish the wizard, visit the roadmap page, vote as a
logged-out visitor.** Must complete on a fresh activate with nothing configured —
`allow_guest_voting` ships **on**, so this is the advertised out-of-box path.
