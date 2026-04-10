# Design System: Contact Us Premium Light

**Project ID:** 5041142492496596494
**Screen ID:** ee0650787593419fb7afbdcf8390ce13
**Dimensions:** 2560x2104 (Desktop)

## 1. Visual Theme & Atmosphere

**Minimalist, Airy, and Approachable**

The contact page embodies a clean, modern aesthetic with generous whitespace and a focus on clarity. The design feels professional yet welcoming, with a two-column layout that balances informational content on the left with an interactive form on the right. The overall atmosphere is light and uncluttered, avoiding heavy visual elements in favor of subtle accents and clear hierarchy.

## 2. Color Palette & Roles

### Primary Colors

- **Vibrant Royal Blue** (#1313ec) - Primary brand color used for interactive elements, icons, and the main CTA button
- **Soft Lavender Background** (#F4F7FF / rgba near #E8ECFF) - Used for the form container card to create subtle visual separation
- **Pure White** (#FFFFFF) - Main page background, creating maximum contrast and breathability

### Text Colors

- **Deep Charcoal** (#0D0D1B or similar dark) - Primary headings and labels
- **Medium Gray** (#64748B or similar) - Secondary text, descriptions, and body copy
- **Icon Background Blue** (Light blue tint, ~#E3E8FF) - Subtle circular backgrounds behind contact icons

### Semantic Colors

- **Icon Blue** (#1313ec) - Contact icons (location, email, phone) rendered in primary blue
- **Border Gray** (Light neutral ~#E2E8F0) - Form input borders, subtle separators

## 3. Typography Rules

**Font Family:** Plus Jakarta Sans (modern geometric sans-serif)

### Hierarchy

- **Page Heading ("Contact Us"):** Extra large, bold weight (font-bold), dominant presence at 3xl-4xl size
- **Section Headings ("Get in Touch"):** Large, bold weight (font-bold), 2xl-3xl size
- **Form Labels:** Semi-bold weight, medium size (base-lg), clear and readable
- **Body Text & Descriptions:** Regular weight, comfortable reading size (base), gray color
- **Contact Details:** Regular weight, slightly subdued color for readability

### Character

Clean, professional, and highly legible. Typography creates clear hierarchy without excessive styling. Letter-spacing is standard, line-height is comfortable for scanning.

## 4. Component Stylings

### Contact Information Items

**Style:** Minimalist list items with left-aligned icons

- **Icon Treatment:** Small circular backgrounds (light blue) containing primary blue Material Symbols icons
- **Layout:** Horizontal flex layout - icon on left, text content on right
- **Spacing:** Icon and text have moderate gap (gap-3 to gap-4)
- **Background:** None - items sit directly on page background
- **Borders:** None
- **Shadows:** None - completely flat styling
- **Typography:** Bold label ("Visit Us", "Email Us", "Call Us") with gray detail text below

### Form Container

**Style:** Soft, elevated card with subtle color

- **Background:** Soft lavender (#F4F7FF or similar light purple tint)
- **Border Radius:** Generously rounded corners (rounded-2xl / 16-20px)
- **Shadow:** Very subtle shadow for slight elevation (shadow-sm to shadow-md)
- **Padding:** Comfortable internal spacing (p-6 to p-8)
- **Borders:** None or extremely subtle border

### Form Inputs

**Style:** Clean, bordered text fields

- **Background:** White or very light background
- **Border:** 1px solid light gray border
- **Border Radius:** Moderately rounded (rounded-lg / 8px)
- **Padding:** Comfortable input padding (px-4 py-2 to py-3)
- **Focus State:** Primary blue ring (ring-2 ring-primary)
- **Label Position:** Above input field
- **Label Style:** Semi-bold, dark text

### Primary Button ("Send Message")

**Style:** Bold, high-contrast call-to-action

- **Background:** Vibrant royal blue (#1313ec)
- **Text Color:** Pure white
- **Border Radius:** Moderately rounded (rounded-lg / 8px)
- **Padding:** Generous horizontal and vertical padding (px-6 py-3)
- **Font Weight:** Semi-bold to bold
- **Hover State:** Slightly darker blue or subtle opacity change
- **Shadow:** None or very subtle

### Social Media Icons

**Style:** Minimal icon links

- **Rendering:** Material Symbols outlined style
- **Color:** Gray or primary blue
- **Layout:** Horizontal row with even spacing
- **Size:** Medium icons (~24px)
- **Hover:** Color change to primary blue

## 5. Layout Principles

### Grid Structure

**Two-Column Layout (Desktop):**

- Left column (~40%): Contact information section with "Get in Touch" heading
- Right column (~60%): Contact form with lavender background card
- Mobile: Stacks vertically, form appears below contact info

### Whitespace Strategy

- **Page Margins:** Generous outer margins (max-w-6xl or max-w-7xl centered container)
- **Section Spacing:** Moderate vertical spacing between page heading and content (mb-8 to mb-12)
- **Element Spacing:** Comfortable gaps between contact items (gap-6 to gap-8)
- **Form Field Spacing:** Consistent vertical rhythm between form fields (mb-4 to mb-6)

### Vertical Rhythm

- Page heading at top with subtitle directly below
- Contact section and form section appear side-by-side at same vertical alignment
- Contact items stack vertically with consistent spacing
- Form fields stack vertically with consistent spacing
- Submit button anchors the bottom of the form

### Container Constraints

- Maximum width constraint keeps content readable (max-w-6xl to max-w-7xl)
- Horizontal padding ensures content doesn't touch viewport edges (px-4 to px-8)
- Vertical padding provides breathing room from header/footer (py-12 to py-16)

## 6. Key Design Patterns

### Information Hierarchy

1. Page title ("Contact Us") with supporting description - centered or left-aligned
2. Side-by-side layout begins:
   - **Left:** "Get in Touch" heading → description → contact items → social icons
   - **Right:** Form heading (if present) → form fields → submit button

### Icon + Text Pattern

Consistent pattern for contact information:

- Small circular background (light blue tint)
- Primary blue icon (Material Symbols: location_on, mail, call)
- Bold label text
- Secondary detail text below

### Form Pattern

Traditional vertical form with:

- Label above each field
- Input field below label
- Consistent spacing between field groups
- Full-width submit button at bottom

## 7. Responsive Behavior

**Desktop (≥768px):**

- Two-column grid layout
- Contact info: left column
- Form: right column
- Navigation: horizontal menu

**Mobile (<768px):**

- Single column stack
- Contact info section appears first
- Form section appears below
- Navigation: collapsed hamburger menu
- Maintained padding and spacing scales down appropriately

## 8. Key Differences from Standard Patterns

1. **No heavy card styling on contact items** - Unlike many contact pages that use prominent cards for each contact method, this design keeps them flat and list-like
2. **Asymmetric two-column layout** - Form takes more width than contact info
3. **Color-coded form background** - The lavender background subtly highlights the interactive area
4. **Minimal elevation** - Very subtle shadows, avoiding heavy drop shadows
5. **Icon circles without borders** - Simple filled circles, not outlined or bordered versions

## 9. Stitch Prompt Vocabulary

To generate similar designs in Stitch, use language like:

- "Two-column layout with contact information on the left and form on the right"
- "Minimalist contact items with small blue icon circles and left-aligned text"
- "Soft lavender background for the form container"
- "Clean, flat design without heavy shadows or card borders"
- "Generous whitespace with a light, airy feel"
- "Primary royal blue for interactive elements and icons"
- "Material Symbols outlined icons for contact methods"
- "Plus Jakarta Sans font for professional, modern typography"
