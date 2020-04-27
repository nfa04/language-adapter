# language-adapter
Language Adapter allows you to load strings in multiple languages based on packages you create, so your webpage is available in the languages you want with the phrases and words you chose.

## Installation
### In your code
Install these files on any place you want. Simply include them by using php's include or require functions on any place you want to use them like this:

```php
require 'path/to/language-adapter/languagePackage.class.php';
require 'path/to/language-adapter/languageReader.class.php';
```
### Packages
#### Creating packages
Before you use languageAdapter you need to define your language packages which languageAdapter will read. Follow these steps to create a directory from which languageAdapter will read its packages:
1. Create a directory, name it as you want to but keep in mind you will need the path later in your code while initializing languageAdapter
2. Create a directory inside the first one for each package you want to provide to languageAdapter and name it by there package name. You can use classic names like this: "en", or you can use complex package names like: "en-US" if you want to provide more details in your packages based on their region. Please Note: This is not a safe method, languageAdapter will work with the information the user's browser provides, these may not equal the actual the real geographic location of the user in some cases.
3. Create a text-file (.txt) for each string you want to provide in their package folder. Name them like this: "stringId.txt". Replace stringId with any stringId you want to. Their content should be the string you want to provide for this stringId. You can skip this step if you want to define them programmatically as explained later in this README file.
4. If you want to change some configuration for languageAdapter globally add a file named: "languagePackages.conf" to the main folder of containing your packages. These are the standard values, if you just want to use these you don't need to have a configuration file:
```
fallbackLanguage=en;
use_downgrade_fallbacks=1;
replace_linebreaks=1;
```
Please note their required values:
- fallbackLanguage: needs to be an existing package name
- use_downgrade_fallbacks: needs to be a boolean like 0 or 1
- replace_linebreaks: needs to be a boolean like 0 or 1

Please note: If you don't want to overwrite their standard values just remove the property from your configuration file. languageAdapter will use their standard value instead.

#### Linking strings inside a string
languageAdapter supports linking a string in another string, it will replace your link with the string value found in the resource you linked. You can do linking like this:
```
String before your link @link:packageName/stringId string after your link
```

If you want to link a string included in the same package you can use the keyword "this" instead of a package name. This action will be faster and more efficient than linking the package name because the package doesn't need to load again. Here is a quick example:
```
String before your link @link:this/stringId string after your link
```

## Usage
### Initializing
Before you use language-adapter you need to initialize it by creating a new reader like this:

```php
$languageReader = new languageReader('path/to/languagePackages');
```

Optionally if you want to change some settings at this point you can do it by this:

```php
$languageReader = new languageReader('path/to/languagePackages', $fallbackLanguage, $use_downgrade_fallbacks, $replace_linebreaks);
```

Note the types of these vars:
- $fallbackLanguage is a string. Its standard value is 'en' for English
- $use_downgrade_fallbacks is a boolean. Its standard value is true
- $replace_linebreaks is a boolean. Its standard value is true

### Reading a specified package
If you know the name of the package you would like to load do this:

```php
$languagePackage = $languageReader->getLanguagePackage('packageName');
```

If you'd not like to use the fallback package if this package doesn't exist, set the second argument to false like this:
```php
$languagePackage = $languageReader->getLanguagePackage('packageName', false);
```

Please Note: In this case this method will return false if the package doesn't exist.

### Get the intranslatable language package
If your packages contain an intranslatable package you can easily load it like this:
```php
$languagePackage = $languageReader->getIntranslatablePackage();
```

### Get the fallback package
If you want to load the defined fallback package you can do this by this:
```php
$languagePackage = $languageReader->getFallbackPackage();
```

### Get the downgrade package for a specified language
If you want to load the downgrade package of a complex language like this: "en-US" (the downgrade package is "en") you can get it by this code:
```php
$languagePackage = $languageReader->getDowngradeLanguagePackage('complexLanguageName');
```

### Get package based on browser language
languageAdapter provides an easy method to get the best package available for your user based on the "HTTP_ACCEPT_LANGUAGE" header of the user's browser. languageAdapter will automatically find the package which fits the users needs most. You can load this package by doing this:
```php
$languagePackage = $languageReader->getAutodetectedLanguagePackage();
```

Please Note: If there is no package available listed in the user's "HTTP_ACCEPT_LANGUAGE" header this method will return the fallback package

### Get an array of all package names found
If you'd like to know which packages languageAdapter has detected use this method:
```php
$languagePackageNamesArray = $languageReader->getPackageList();
```

### Get an array of all packages found
If you want to load every package languageAdapter has detected you can do it by this:
```php
$languagePackagesArray = $languageReader->getPackages();
```

### Get string from languagePackage
You can get a string from a languagePackage by this method:
```php
$string = $languagePackage->getString('stringId');
```

### Print string directly from languagePackage
If you want to print the string directly and not just get it you can do this by this:
```php
$languagePackage->printString('stringId');
```

Please note: This method won't return any value its a void method

### Set a string in a languagePackage
If you'd like to set a new string or you'd like to overwrite it's old value you can do this by the following code:
```php
$languagePackage->setString('stringId', 'stringValue');
```
If you don't want to overwrite any value if this string exists, set the third argument to be false:
```php
$languagePackage->setString('stringId', 'stringValue', false);
```
