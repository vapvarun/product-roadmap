# Feature Page: Voting System

## Page Copy

---

## HERO SECTION

### Headline
**Real Votes. Real Priorities.**

### Subheadline
Stop guessing what customers want. Let them vote on features and show you exactly what to build next.

### CTA
**Download Free Plugin** | **See Demo**

---

## OVERVIEW SECTION

### Content
Every product team struggles with the same question: "What should we build next?"

Surveys give you biased answers. Support tickets miss the big picture. And gut feeling? That's just expensive guessing.

Product Roadmap's voting system changes everything. Customers upvote features they want and downvote ones they don't. You get real data - not opinions, not guesses, just clear priorities based on actual demand.

---

## HOW IT WORKS SECTION

### Section Header
**Voting Made Simple**

### For Your Customers

**1. Browse Your Roadmap**
Customers see your public roadmap with all planned features.

**2. Click to Vote**
One click on the upvote arrow registers their vote. Count updates instantly.

**3. Change Their Mind**
Customers can remove their vote or switch to downvote anytime.

**4. See Results**
Everyone sees vote counts, creating social proof for popular features.

### For You

**1. Track Votes**
See vote counts on every item in your admin dashboard.

**2. Sort by Popularity**
Filter items by most-voted to identify top priorities.

**3. Make Decisions**
Use real data to guide your roadmap decisions.

**4. Communicate Clearly**
Tell customers "Based on your votes, we're building X."

---

## FEATURE DETAILS SECTION

### Upvote/Downvote System

Unlike simple "like" buttons, Product Roadmap uses real voting:
- **Upvote** - "I want this feature"
- **Downvote** - "I don't think this is important"

This gives you nuanced data. A feature with 50 upvotes and 10 downvotes tells a different story than one with 60 upvotes and 0 downvotes.

---

### Guest Voting

**Remove friction. Increase participation.**

Guest voting is enabled by default. Customers don't need to create an account - they just vote.

- No registration required
- IP-based tracking prevents duplicates
- One vote per person per item
- Optional: Require login if needed

**Why it matters:** Every extra step reduces participation. Guest voting can 10x your vote counts.

---

### StackOverflow-Style UI

The single item detail page features a professional voting interface inspired by StackOverflow:

- Large, prominent vote count
- Clear up/down arrows
- Visual feedback on hover
- Color change when voted
- Familiar pattern users recognize

---

### Real-Time Updates

Votes register instantly:
- Count updates without page reload
- Visual feedback confirms the action
- No waiting, no confusion
- Smooth animations

---

### Sorting & Filtering

Find what matters with built-in sorting:
- **Most Voted** - Top priorities first
- **Newest** - Latest items
- **Recently Active** - Recent engagement
- Filter by status, category, or product (Pro)

---

## BENEFITS SECTION

### Section Header
**Why Voting Matters**

### Data-Driven Decisions
Stop building features nobody uses. Voting data shows you exactly what customers want before you invest development time.

### Reduced Development Waste
Industry studies show 40% of features go unused. Voting helps you build the 60% that matters.

### Customer Engagement
When customers can vote, they feel invested in your product's success. They're not just users - they're participants.

### Transparent Communication
"We built this feature because you voted for it" is powerful messaging. Voting creates accountability and trust.

### Stakeholder Evidence
Need to justify roadmap decisions to leadership? Show them the vote counts. Data speaks louder than opinions.

---

## USE CASES SECTION

### Section Header
**Voting in Action**

### SaaS Product Team
*"We had 50 feature requests from different channels. After adding voting, it became clear that 3 features had 10x more demand than others. We shipped those first and customer satisfaction jumped."*

### WordPress Plugin Developer
*"My support forum was full of feature requests. Now users vote instead of creating duplicate tickets. I know exactly what to build for each release."*

### Agency Client Management
*"Clients used to debate scope endlessly. Now we put features on the roadmap and let stakeholders vote. The data ends the arguments."*

---

## COMPARISON SECTION

### Section Header
**Voting vs. Alternatives**

| Method | Data Quality | Customer Effort | Your Effort |
|--------|--------------|-----------------|-------------|
| **Voting** | High | Low | Low |
| Surveys | Medium | High | Medium |
| Support Analysis | Low | None | High |
| Social Polls | Medium | Medium | Medium |
| Gut Feeling | None | None | None |

---

## TECHNICAL DETAILS

### How Votes Are Stored
- Custom database table for votes
- Links vote to user ID (logged in) or IP address (guests)
- Stores timestamp for trend analysis
- Efficient queries even with thousands of votes

### Vote Limits
- One vote per user per item
- Changing vote replaces previous
- No rate limiting needed

### Permissions
- Control who can vote in Settings
- Options: Everyone, logged-in users only
- Customize with filters for advanced needs

---

## FAQ SECTION

### Q: Can someone vote multiple times?
**A:** No. Each person gets one vote per item. Logged-in users are tracked by user ID, guests by IP address.

### Q: Can I disable downvoting?
**A:** Not currently. In practice, downvotes are rare - most users either upvote or don't vote at all.

### Q: Do votes carry over if I upgrade to Pro?
**A:** Yes. All vote data remains intact when you add the Pro addon.

### Q: Can I see who voted for what?
**A:** Currently, voting is anonymous to encourage participation. The admin sees aggregate counts, not individual voters.

### Q: How do I encourage more voting?
**A:** Enable guest voting (default), share your roadmap link widely, and respond to voted items to show votes matter.

---

## CTA SECTION

### Headline
**Start Collecting Votes Today**

### Content
The free version includes the full voting system. Download, set up in 5 minutes, and start getting real feedback from customers.

### Buttons
**Download Free Plugin** | **View Documentation**

---

## RELATED FEATURES

- [User Suggestions](/features/suggestions) - Let customers submit their own ideas
- [Analytics Dashboard](/features/analytics) (Pro) - Track voting trends over time
- [Public Roadmap](/features/roadmap) - Display your plans with voting enabled
