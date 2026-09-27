# Voting and Comments

**Time to read: 6 minutes**

Learn how to enable and manage user engagement through voting and comments.

## What You'll Learn

- How voting works
- Enabling and configuring voting
- Managing comments
- Handling feature suggestions
- Best practices for engagement

## Understanding Voting

### What is Voting?

Voting lets users show which features they want most. Each user can vote once per item.

**Why enable voting?**
- **Prioritize development** - Build what users want
- **Reduce guessing** - Data-driven decisions
- **Engage users** - Make them feel heard
- **Validate ideas** - Test demand before building

### How Voting Works

**For Users:**
1. Click vote button on any item
2. Vote count increases
3. Button changes to a clear "Voted" state (checkmark + pill)
4. Click again to remove vote

Since 1.7.0 the "Voted" state stays visible even on themes that heavily restyle buttons.

**For Admins:**
1. See vote counts on all items
2. Sort by votes to find popular items
3. View who voted (in item edit screen)
4. Use votes to prioritize roadmap

### Voting UI (v1.3.0)

**StackOverflow-style Single Item Pages:**
- Voting section displayed prominently on the left side
- Up arrow: Add your vote (green hover)
- Down arrow: Remove your vote (orange hover when voted)
- Large vote count display
- Clear visual feedback for vote states

**Hover Colors:**
| State | Color | Meaning |
|-------|-------|---------|
| Not voted + hover up | Green | "Add your vote" |
| Already voted | Solid green | Vote registered |
| Voted + hover down | Orange | "Remove your vote" |

**Idea Box Template (Pro):**
- Dedicated upvote button per idea
- Click to vote, click again to unvote
- Sort by "Top Voted", "Newest", or "Active"
- Requires the Pro addon; free installs use the minimal layout

## Setting Up Voting

### Enable Voting

1. Go to **Roadmap > Settings**
2. Click **General** tab
3. Find **User Interaction** section
4. Check ☑ **Enable Voting**
5. Click **Save Settings**

Voting is now active!

### Voting Options

**Require Login for Voting**
- ☑ **Enabled** (Recommended)
  - Only logged-in users can vote
  - Prevents spam and duplicates
  - Tracks who voted

- ☐ **Disabled**
  - Guest users can vote
  - Tracked by IP address
  - Less reliable
  - Higher spam risk

**Guest Voting Settings**

If you allow guest voting:

1. **Roadmap > Settings**
2. Enable guest voting
3. Guest votes are tracked by IP address to limit duplicates

**Spam Defense (v1.7.0, automatic)**

Votes and suggestions are rate-limited (per user, or per IP for guests), and the suggestion form includes a hidden honeypot field to catch bots. There is nothing to configure - these protections are always on.

### Where Votes Appear

**Frontend:**
- Vote count badge on item cards
- Vote button on each item
- "Voted" state when user has voted

**Admin:**
- Vote count column in item list
- Voter list in item edit screen
- Sort by votes in admin list

## Managing Votes

### Viewing Voters

See who voted on an item:

1. Edit the roadmap item
2. Scroll to **Roadmap Statistics** meta box
3. See total vote count
4. Click to expand voter list (if available)

### Removing Votes

**Users remove their own:**
- Click vote button again
- Vote is removed immediately

**Admin removes vote:**
- Edit the item
- Find voter in statistics
- Remove manually (if plugin supports)

### Sorting by Votes

**In Admin:**
1. **Roadmap > All Items**
2. Click **Votes** column header
3. Items sorted by vote count

**On Frontend:**
Use the `sort` shortcode parameter:
```
[roadmap product="main" sort="votes"]
```

Shows most-voted items first.

## Understanding Comments

### What are Comments?

Comments let users discuss roadmap items, ask questions, and provide feedback.

**Why enable comments?**
- **Gather details** - Users explain what they need
- **Answer questions** - Clarify features
- **Build community** - Create discussions
- **Collect feedback** - Learn about pain points

### How Comments Work

Uses standard WordPress comment system:
- Same as blog post comments
- Threaded replies supported
- Moderation tools available
- Email notifications
- Spam filtering (with Akismet)

## Setting Up Comments

### Enable Comments Globally

1. **Roadmap > Settings**
2. Click **General** tab
3. Check ☑ **Enable Comments**
4. Click **Save Settings**

### WordPress Comment Settings

Configure comment behavior:

1. **Settings > Discussion**
2. Check these options:
   - ☑ Allow people to submit comments
   - ☑ Comment author must fill out name and email
   - ☐ Users must be registered to comment (optional)
   - ☑ Comments must be manually approved (recommended)

3. **Email notifications:**
   - ☑ Email me when comment is posted
   - ☑ Email me when comment is held for moderation

4. Click **Save Changes**

### Per-Item Comment Settings

Turn comments on/off for specific items:

1. Edit roadmap item
2. Find **Discussion** meta box (right sidebar)
3. Check/uncheck:
   - ☑ Allow comments
   - ☑ Allow pingbacks and trackbacks

If you don't see Discussion box:
- Click **Screen Options** (top right)
- Check ☑ Discussion

## Managing Comments

### Viewing All Comments

1. Go to **Comments** in WordPress admin
2. See all comments across all items
3. Filter by roadmap items if needed

### Moderating Comments

**Approve comment:**
1. Hover over comment
2. Click **Approve**
3. Comment becomes visible

**Reply to comment:**
1. Hover over comment
2. Click **Reply**
3. Type response
4. Click **Reply**

**Mark as spam:**
1. Hover over comment
2. Click **Spam**
3. Sends to spam folder

**Delete comment:**
1. Hover over comment
2. Click **Trash**
3. Moves to trash (can restore)

### Quick Actions

**Bulk approve:**
1. Check multiple comments
2. Select "Approve" from dropdown
3. Click **Apply**

**Bulk delete:**
1. Check multiple comments
2. Select "Move to Trash"
3. Click **Apply**

### Comment Notifications

Get email when comments posted:

1. **Settings > Discussion**
2. Check notification options
3. Enter admin email
4. Save

Email includes:
- Commenter name
- Comment text
- Link to approve/spam/trash

## Feature Suggestions

### What are Suggestions?

Let users submit their own feature ideas. Suggestions become roadmap items.

**Why enable suggestions?**
- **Crowdsource ideas** - Users suggest features
- **Discover needs** - Learn what users want
- **Save time** - Users create draft items
- **Engage community** - Make users feel valued

### Enabling Suggestions

1. **Roadmap > Settings**
2. **General** tab
3. Check ☑ **Enable Feature Suggestions**
4. Configure options:
   - ☑ **Require Login** (recommended)
   - **Default Status**: "Under Review"
   - **Notification Email**: Your email
5. Click **Save Settings**

### How Users Submit Suggestions

1. User clicks **Suggest a Feature** button
2. Fills out form:
   - Title (required)
   - Description (required)
   - Category (optional)
   - Email (if guest submission)
3. Clicks **Submit**
4. Sees confirmation message

### Managing Suggestions

**Find suggestions:**
1. **Roadmap > All Items**
2. Filter by Status: "Under Review"
3. Review each suggestion

**Approve suggestion:**
1. Open suggestion item
2. Edit title/description if needed
3. Change status to "Planned" or "Idea"
4. Set priority
5. Click **Update**

**Reject suggestion:**
1. Open suggestion
2. Option A: Change status to "Cancelled"
3. Option B: Move to Trash
4. Add comment explaining why (optional)

**Merge duplicates:**
1. Find similar suggestions
2. Keep best one
3. Add comment: "Merged with #123"
4. Trash others

### Suggestion Notifications

You'll receive email with:
- Title and description
- Submitter name/email
- Link to edit item
- Auto-created in "Under Review" status

## Best Practices

### Encouraging Voting

**Promote voting:**
- Add call-to-action: "Vote for features you want!"
- Mention in newsletters
- Show vote counts prominently
- Thank top voters

**Respond to votes:**
- Build high-voted features
- Comment on progress
- Explain why you can't build something
- Show votes influence roadmap

### Managing Comments

**Respond quickly:**
- Reply within 24-48 hours
- Acknowledge feedback
- Answer questions
- Thank for input

**Keep discussions on track:**
- Mark off-topic as spam
- Redirect to support for bugs
- Moderate appropriately
- Set clear guidelines

**Use comments to improve:**
- Note common questions in description
- Clarify confusing features
- Gather requirements
- Build community

### Handling Suggestions

**Review regularly:**
- Check weekly
- Process promptly
- Give feedback
- Keep users informed

**Communicate decisions:**
- Explain why accepted
- Explain why rejected
- Suggest alternatives
- Credit submitters

**Thank submitters:**
- Comment on their suggestion
- Mention in updates
- Give credit when built
- Build trust

## Engagement Tips

### Build Trust

**Be transparent:**
- Update statuses regularly
- Explain delays
- Share progress
- Admit mistakes

**Keep promises:**
- Deliver on commitments
- Update target dates
- Communicate changes
- Under-promise, over-deliver

### Create Community

**Recognize contributors:**
- Thank voters
- Credit suggestion authors
- Highlight feedback
- Feature top voters

**Foster discussion:**
- Ask questions in comments
- Poll users
- Share mockups
- Request feedback

### Reduce Noise

**Set expectations:**
- Explain voting purpose
- Define suggestion criteria
- Share decision process
- Clarify timelines

**Moderate fairly:**
- Consistent rules
- Clear guidelines
- Quick responses
- Fair treatment

## Troubleshooting

### Voting Not Working

**Check settings:**
- Voting enabled in Settings
- Check user permissions
- Test with different browsers
- Clear cache

**JavaScript errors:**
- Open browser console (F12)
- Look for errors
- Disable conflicting plugins
- Test with default theme

### Comments Not Showing

**Check settings:**
- Comments enabled in Roadmap Settings
- WordPress Discussion settings configured
- Per-item comments enabled
- Database tables exist

**Test comments:**
- Try posting as admin
- Try as logged-in user
- Try as guest (if allowed)
- Check spam folder

### Suggestions Not Creating Items

**Verify setup:**
- Suggestions enabled in settings
- Email notifications working
- Database permissions correct
- Check for PHP errors

**Find submissions:**
- Check All Items
- Filter by Status
- Check Trash
- Look in Draft status

## Next Steps

- **Configure Settings** - Fine-tune all options
- **Display Roadmap** - Show to users
- **Analyze Data** - Use votes to prioritize

---

← Statuses & Categories | **Displaying Roadmap** →