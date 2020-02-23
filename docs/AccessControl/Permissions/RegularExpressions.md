---
currentMenu: permissions
---
# Powerful permission rules with regular expressions

## A look a little deeper

Both components and instances can be described using regular expressions. Here lies part of the flexibility and power of the permission system.

First, it is possible to use a period with asterisk as a wildcard to designate a section as *everything* - so `.*` just means *all instances*.

Furthermore, colons are used for both components and instances to separate three sections or divisions, as written above. Each section can be described by a term. If one term is specific enough, the rest are also assumed to mean *everything*, though the wildcard is not required.

So there are two shorthand notations: asterisks can be omitted in specific references and colons can be omitted for global references.

Here are concrete examples to make that clear:

- `.*` is equal to `.*:.*:.*`
- `MyComponent::` is equal to `MyComponent:.*:.*`

For a first impression of how flexible this notation can be used consider a fictional `AcmeRecipesModule` which allows to manage recipes, ingredients and reviews. Here are some possible applications for different components (each with instance set to `.*`):

- `AcmeRecipesModule::` applies for all entities in the recipes database.
- `AcmeRecipesModule:Recipe:` applies for all recipe entities, but not for ingredients or reviews.
- `AcmeRecipesModule:(Recipe|Ingredient):` applies for all recipe and ingredient entities, but not for reviews.

A first simple example for a specific instance would be:

- `AcmeRecipesModule:Recipe:` with `1::` applies only for the recipe entity with ID `1`.
- `AcmeRecipesModule:Recipe:` with `Delicious cookie::` applies only for the recipe entity with title `Delicious cookie`.

Which of them or if both are applicable depends on how `AcmeRecipesModule` uses and interprets the permission rules.

## Advanced examples

Let's venture into slightly more complex applications. The following more sophisticated examples will not be used in daily business, but could become handy if needed.

### Using multiple divisions

As shown in the previous section it is quite common to have a component consisting of multiple levels which helps to target not an entire extension as a whole, but only specific sub components or content types within that extension.

Using multiple levels in the instance part is a less common scenario because it makes only sense for use cases when instances may occur in different constellations.

Let's assume that some users must neither see nor read the ingredient page of *Sugar*, but they should be able to see everything related to *Sugar* within the recipe for the delicious cookies from above.

One possible realisation for that requirement could look like this:

- `AcmeRecipesModule:Recipe:RecipeIngredient` with `Delicious cookie:Sugar:` and `ACCESS_READ`

Nested instances therefore could make sense if different contexts need to be differentiated for the same data.

Of course it would also be possible to use multiple different components and/or combinations of components. Again, how a specific extension applies this in its implementation is individual.

### Hook providers

Extensions offering [hook providers](../../Development/Hooks/README.md) have extended requirements. Because they could want to support different permission schemes depending on the extensions that are connected as hook subscribers. The idea is that it is possible to define permissions for attached comments / reviews / files / etc. based on the objects where these are attached to.

Hence, it makes sense to use the subscriber's name as first level for the instance part.

The following examples are for a `AcmeFileManagerModule` that allows to attach uploads and files to other extensions.

The three instance levels could be used like this:

1. the subscriber extension
2. the object identifier, that is the ID of the connected object, like a news article, a forum topic, a recipe etc.
3. the file identifier, that is the ID of an uploaded and/or attached file

This scheme would lead to the following possible examples for the instance part of corresponding permission rules:

- `AcmeForumModule:6:` targets all files attached to the forum topic with ID 6
- `AcmeForumModule:6:(3|5)` targets only files 3 and 5 in topic 6
- `AcmeNewsModule:(3|4|5):` targets all files in articles 3, 4 and 5
- `AcmeRecipesModule::` targets all files in the recipes database
- `AcmeNewsModule:[^34]:` targets all files in all articles except 3 und 4
- `AcmeRecipesModule:\d*[^34]\d*` all except those which IDs contain 3 or 4

### The ANY instance

If the instance part is set to `ANY` this causes a special processing. The permissions system interprets this as the user has ANY access to the given component, without the need to determine the exact instance. In practice this results in that the permission levels of all permission rules for that component are collected *until* the first one whose instance matches `::` (or `''` or `:::::`, doesn't matter). Afterwards the highest permission level that has been collected is granted.

This special syntax may be useful in rare cases but should always be used with care.
