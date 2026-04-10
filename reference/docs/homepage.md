1. Global Style System (Design Tokens)
   Color Palette:

Primary (Blue): #2D31FA (Buttons, active states, accents)
Primary Dark (Hover): #1A1DBA
Secondary (Light Blue/Gray): #F4F7FF (Section backgrounds, card backgrounds)
Text Primary: #0F172A (Headings and main body)
Text Secondary: #475569 (Subtitles and labels)
Success/Progress: #10B981 (Green for winners/high progress)
Neutral White: #FFFFFF
Typography:

Headings (Inter or Montserrat): SemiBold/Bold, -0.02em letter spacing.
H1 (Hero): 64px / 1.1 line-height
H2 (Section): 40px / 1.2 line-height
H3 (Card): 20px / 1.4 line-height
Body: 16px / 1.6 line-height (Regular)
Labels/Badges: 12px / Bold / Uppercase / 0.05em spacing.
Shadows & Radius:

Large Cards: box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
Border Radius: 16px (Standard for cards/banners), 12px (Buttons), 50% (Avatars). 2. Layout & Grid Constraints
Max Container Width: 1280px
Outer Padding: 32px (Desktop), 16px (Mobile)
Section Gutter (Vertical): 120px spacing between major sections.
Grid System: 12-column grid. Competition cards use 4 columns (3-up on desktop). 3. Component Specifications
A. Navigation Header
Height: 80px
Behavior: Fixed/Sticky on scroll with a slight backdrop-filter: blur(8px) and border-bottom: 1px solid #E2E8F0.
CTA Button: 12px 24px padding, primary blue background.
B. Hero Section
Visual: Split layout. Text content (Col 1-5), Prize Image (Col 7-12).
Image Treatment: Rounded corners 24px. Include a floating badge (Last Winner) with absolute positioning: bottom: 40px, left: -40px.
Buttons: Secondary button (Recent Winners) uses border: 1px solid #E2E8F0.
C. Competition Cards (The "Ending Soon" Grid)
Dimensions: Aspect ratio for image 16:10.
Progress Bar:
Outer: height: 6px, background #F1F5F9.
Inner: Primary blue, width based on tickets_sold / total_tickets.
Price Badge: Positioned absolute top-right of the image area. Padding 8px 12px, background white with backdrop-filter.
Hover State: transform: translateY(-4px), increase shadow depth.
D. Promotion Banner
Background: Gradient from #1E1B4B to #2D31FA with an overlay of a high-quality luxury lifestyle image (opacity 20%).
Padding: 80px vertical.
E. Testimonial Winners ("Stories of the Circle")
Layout: 2-column flexbox or grid.
Card Background: White to Light Gray gradient (#FFFFFF to #F8FAFC).
Quote Styling: Large decorative quote icons in light gray. 4. Responsive Rules
Tablet (1024px):
Scale H1 to 48px.
Competition grid switches to 2-up.
Mobile (768px):
Stack all side-by-side elements vertically.
H1 to 36px.
Side-scroll enabled for the "Ending Soon" cards instead of a multi-row grid.
Header CTA moves into a hamburger menu. 5. Interaction States
Buttons: Transition all 0.2s ease-in-out.
Links: Underline animation or color shift to Primary Blue.
FAQ: Accordion style. One item open at a time. Transition height from 0 to auto.
