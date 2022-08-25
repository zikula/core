---
currentMenu: themes
---
# Theme configuration

File: `/Resources/config/theme.yaml`

Status: Required

Must: define a 'master' realm

Description: define various 'realms' within a theme. Within a realm, a pattern is defined that is used by regex
to match the (1) path, (2) route id, or (3) the bundle name. The realms are matched from top to bottom returning the
first match (case-insensitive). Therefore, more specific definitions must be higher than general definitions.

Three additional 'alias' realms may be defined and neither requires a pattern:
  1) Defining an 'admin' realm will be used when `#[Theme('admin')]` controller method attribute is detected in the method
  2) Defining a 'home' realm will be used when the path = `/`
  3) Defining an 'error' realm will be used when an exception is thrown (other than AccessDeniedException) and the error
     template is rendered.

Any block positions in the page's template must be defined here.

Do not duplicate realm names or later entries will override previous entries

## Optional Values

- bootstrapPath: set a zasset-type path to override the core bootstrap.css - the file is then
  stored in your Theme's `Resources/public/css/` directory
  - e.g.: `bootstrapPath: "@ZikulaDefaultThemeBundle:css/cerulean.min.css"`
