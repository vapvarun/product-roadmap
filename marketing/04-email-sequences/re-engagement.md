# Re-Engagement Email Sequence

## Overview
3-email sequence to re-activate users who haven't engaged recently

**Goal:** Bring inactive users back, update them on improvements, win back churned users
**Timing:** After 30-60 days of inactivity
**Trigger:** No opens/clicks for 30+ days, no plugin activity

---

## TRIGGER CRITERIA

### For Free Users
Send when user has:
- Not opened emails in 30+ days
- OR downloaded plugin but no recent activity signals
- AND not unsubscribed

### For Pro Users
Send when user has:
- Not opened emails in 30+ days
- OR no plugin update activity in 60+ days
- AND license still active

---

## EMAIL 1: "We've Missed You"
**Send:** Trigger + 0 days
**Subject Lines (A/B test):**
- "Still using Product Roadmap? Here's what's new"
- "We've added some things you might like"
- "Quick update on Product Roadmap (you've missed a few things)"

### Content

---

**Subject:** Still using Product Roadmap? Here's what's new

Hi {first_name},

It's been a while since we connected. I wanted to share what's new with Product Roadmap in case you missed it.

**Recent updates:**

**[Feature 1]** (if recent)
[One sentence description]

**[Feature 2]** (if recent)
[One sentence description]

**[Improvement]**
[One sentence description]

[Button: See What's New]

**Are you still using Product Roadmap?**

If yes - great! Make sure you're on the latest version for all the improvements.

If no - I'd love to know why. Reply to this email and let me know what happened. Your feedback helps us improve.

{signature}

P.S. - If you're not interested in emails anymore, no problem. [Unsubscribe link] - no hard feelings.

---

## EMAIL 2: Value Reminder
**Send:** Trigger + 5 days (if no engagement with Email 1)
**Subject Lines:**
- "Remember why you downloaded Product Roadmap?"
- "Your customers have feedback - are you collecting it?"
- "The feature voting your competitors wish they had"

### Content

---

**Subject:** Remember why you downloaded Product Roadmap?

Hi {first_name},

When you downloaded Product Roadmap, you probably had a goal: collect customer feedback, prioritize features, build products people actually want.

**Is that still a priority?**

If yes, here's a quick reminder of what you can do:

**Collect votes** - Know exactly which features customers want most
**Gather suggestions** - Capture ideas directly from users
**Build transparency** - Show customers what you're working on
**Track progress** - Document releases with the changelog

[Button: Set Up Your Roadmap]

**Need help getting started?**
- [Setup guide](link) - 5 minutes to launch
- [Video tutorial](link) - See it in action
- [Documentation](link) - Full reference

**Or maybe things changed?**

If Product Roadmap isn't right for you anymore, I'd genuinely like to understand why. Reply and let me know - it helps us improve.

{signature}

---

## EMAIL 3: Last Chance
**Send:** Trigger + 10 days (if no engagement with Emails 1 & 2)
**Subject Lines:**
- "Should I stop emailing you about Product Roadmap?"
- "Last check: Still interested in Product Roadmap?"
- "One click: Stay or go"

### Content

---

**Subject:** Should I stop emailing you about Product Roadmap?

Hi {first_name},

I've sent a couple emails recently and haven't heard back. I want to respect your inbox.

**Quick question: Are you still interested in Product Roadmap?**

[Button: Yes, keep me updated] → Stays on list
[Button: No, unsubscribe me] → Unsubscribe

If I don't hear from you, I'll assume you'd like fewer emails and move you to our occasional newsletter (major updates only).

**Before you go (if you're going):**

Could you tell me why? Just reply with a number:
1. I'm not building products that need roadmaps
2. I found a different solution
3. I tried it but it didn't work for me
4. I'm too busy right now
5. Other reason

Your feedback helps us improve. Even a quick number helps.

Thanks either way!

{signature}

---

## POST-SEQUENCE ACTIONS

### If Engaged (clicked "Yes, keep me updated")
- Keep on regular list
- Reset engagement tracking
- Tag as "re-engaged"

### If Clicked "No" or Unsubscribed
- Unsubscribe immediately
- Tag reason if provided
- Add to win-back suppression (6+ months)

### If No Response After Sequence
- Move to low-frequency list (quarterly updates only)
- Or remove from promotional emails entirely
- Keep transactional emails (license, security)

---

## WIN-BACK VARIATIONS

### For Churned Pro Customers
**Trigger:** License expired, no renewal
**Different approach:**

---

**Subject:** Your Pro license expired - here's what you're missing

Hi {first_name},

Your Roadmap Pro license expired [X days/months] ago. You can still use the features, but you're missing:

- New features we've added since then
- Bug fixes and improvements
- Priority support access

**What's new since you left:**
- [New feature 1]
- [New feature 2]
- [Improvement]

[Button: Renew Your License]

If Pro isn't right for you anymore, the free version still works great. But if you want the analytics, timeline, and multi-product features back with support, we're here.

{signature}

---

### For Users Who Deactivated Plugin
**Trigger:** Deactivation tracking (if available)

---

**Subject:** Noticed you deactivated Product Roadmap - everything ok?

Hi {first_name},

I noticed Product Roadmap was deactivated on your site. I wanted to check in.

**Did something go wrong?**
- Technical issue → [Support link]
- Didn't fit your needs → Reply and tell me more
- Just testing → No worries!

**Did you find something better?**
Honest feedback helps us improve. What did the alternative do that we didn't?

If you're open to giving it another try, here's what's improved recently:
[List 2-3 recent improvements]

[Button: Reinstall Product Roadmap]

Either way, thanks for trying Product Roadmap. I hope we can help you in the future.

{signature}

---

## SEQUENCE METADATA

### Timing
- Email 1: Trigger + 0 days
- Email 2: Trigger + 5 days (if no engagement)
- Email 3: Trigger + 10 days (if no engagement)

### Engagement Definition
- Open OR click counts as engagement
- Engagement on any email stops the sequence
- Engagement moves them back to active list

### Tracking
- Re-engagement rate (engagement on any email)
- Unsubscribe rate
- Reason codes (from Email 3)
- Plugin reactivations (if trackable)

### Suppression
- Don't send to users who purchased Pro in last 30 days
- Don't send to users who submitted support ticket in last 14 days
- Don't send more than once per 90 days
