---
currentMenu: content-blocks
---
# Blocks

Zikula now includes the StaticContentModule as a Value-Added extension in Core-3.1.0.
With this, standard blocks exist in Zikula to create and display basic content.

- HTML Block
- Text Block
- Xslt Block
- File Include Block
- **New in Core 3.1.0 - Template Block**

## HTML Block

Renders and displays basic HTML content.

## Text Block

Renders text only. Strips all tags and converts new lines to breaks.

## Xslt Block

Parses and displays an XML document using PHP's XSL extension.

## File Include Block

Displays a file when provided with a path to the file.

## Template Block

**New in Core 3.1.0**
Renders a twig template when provided with a path to the template.

### More blocks

Other extensions can provide additional content-providing blocks to Zikula. For example, the EZComments Module
provides blocks to display recent comments and the most commented content. Check out other extensions for more!
