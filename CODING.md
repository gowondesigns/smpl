# PHP Coding Guidelines & Best Practices

Coding Standards are an important factor for achieving a high code quality. A common visual style, naming conventions and other technical settings allow us to produce a homogenous code which is easy to read and maintain. However, not all important factors can be covered by rules and coding standards. Equally important is the style in which certain problems are solved programmatically - it's the personality and experience of the individual developer which shines through and ultimately makes the difference between technically okay code or a well considered, mature solution. 

These guidelines try to cover both, the technical standards as well as giving incentives for a common development style. These guidelines must be followed by everyone who creates code for the SMPL Content Management System. We hope that you feel encouraged to follow these guidelines as well when creating your own packages and SMPL-based applications.

__Note: These guidelines have been adapted from those utilized by the TYPO3 Association documentation. Their PHP Coding and Style guide can be found [here][1].__

## Code Formatting and Layout, *Beautiful Code*

The visual style of programming code is very important. In the SMPL project we want many programmers to contribute, but in the same style. This will help us to:

*   Easily read/understand each others code and consequently easily spot security problems or optimization opportunities
*   It is a signal about consistency and cleanliness, which is a motivating factor for programmers striving for excellence

Some people may object to the visual guidelines since everyone has their own habits. You will have to overcome that in the case of SMPL; the visual guidelines must be followed along with coding guidelines for security. We want all contributions to the project to be as similar in style and as secure as possible.

### General Considerations

*   Almost every PHP file in SMPL contains exactly one class and does not output anything if it is called directly. Therefore you start your file with a `<?php` tag and end it with the closing `?>`.
*   Every file must contain a [PHPDoc][2] header stating package and licensing information

*The SMPL standard file header*:

    <?php
    /**
    * Class.Date
    *
    * @package SMPL\Date
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3
    */

*   Code lines are of arbitrary length, no strict limitations to 80 characters or something similar (wake up, graphical displays have been available for decades now...). But feel free to break lines for better readability if you think it makes sense!
*   Lines end with a newline a.k.a chr(10) - UNIX style
*   Files must be encoded in UTF-8 without byte order mark (BOM)

#### Indentation and Line Formatting

Indentation is done with tabs - and not spaces! The beginning of a line is the only place where tabs are used, in all other places use spaces. Always trim whitespace off the end of a line.

*Here's a code snippet which shows the correct usage of tabs and spaces*:

    /**
     * Returns the name of the currently set context.
     *
     * @return string Name of the current context
     * @author Your Name <your@email.here>
     */
    public function getContextName() {
        return $this->contextName;
    }
    

There seem to be very passionate opinions about whether TABs or spaces should be used for indentation of code blocks in the scripts. If you'd like to read more about this religious discussion, you find some nice arguments in the [Joels on Software][3] forum.

### Naming

Naming is a repeatedly undervalued factor in the art of software development. Although everybody seems to agree that nice names are a nice thing to have, most developers choose cryptic abbreviations in the end (to save time/space). Opting to save time/space, however, can lead to hours of confusion or impossible to understand code in the future. Better class/method/property names in the project lead to better readable, manageable, stable and secure code.

As a general note, English words (or abbreviations if necessary) should be used for all class names, method names, comments, variables names, database table and field names. When using abbreviations or acronyms remember to make them camel-cased as needed, avoid capitalizing everything (SMPL vs. Smpl).

### Package Names

All package names start with an uppercase character and usually are written in UpperCamelCase. In order to avoid problems with different filesystems, only the characters a-z, A-Z, 0-9 and the dash sign "-" are allowed for package names – don't use special characters.

The full package key is then built by combining the vendor namespace and the package, like SMPL\Fluid or Acme\Demo.

### Namespace and Class Names

*   Only the characters a-z, A-Z and 0-9 are allowed for class names.
*   Class names are always written in UpperCamelCase.
*   The unqualified class name must be meant literally even without the namespace.
*   The main purpose of namespaces is categorization and ordering
*   Class names must be nouns, never adjectives.
*   The name of abstract classes must start with the word "Abstract", class names of aspects must end with the word "Aspect".

### Interface Names

Only the characters a-z, A-Z and 0-9 are allowed for interface names – don't use special characters. Use adjectives whenever possible for Interface names, otherwise do not use any additional modifications. Always avoid naming tautologies like *IClass*, or *ClassInterface*. All interface names are written in UpperCamelCase.

*A few examples:*

*   \SMPL\Core\Object\Truck
*   \SMPL\Core\Object\Queryable
*   \MyCompany\MyPackage\Content
*   \MyCompany\MyPackage\MyObject\Parseable

### Exception Names

Exception naming basically follows the rules for naming classes. There are two possible types of exceptions: generic exceptions and specific exceptions. Generic exceptions should be named "Exception" preceded by their namespace. Specific exceptions should reside in their own sub-namespace end with the word Exception.

*   \SMPL\Core\Object\Exception
*   \SMPL\Core\Object\Exception\InvalidClassNameException
*   \MyCompany\MyPackage\MyObject\Exception
*   \MyCompany\MyPackage\MyObject\Exception\OutOfCoffeeException

### Method Names

All method names are written in lowerCamelCase. In order to avoid problems with different filesystems, only the characters a-z, A-Z and 0-9 are allowed for method names – don't use special characters.

Make method names descriptive, but keep them concise at the same time. Constructors must always be called __construct(), never use the class name as a method name.

*   `myMethod()`
*   `someNiceMethodName()`
*   `betterWriteLongMethodNamesThanNamesNobodyUnderstands()`
*   `singYmcaLoudly()`
*   `__construct()`

### Variable Names

Variable names are written in lowerCamelCase and should be:

*   self-explanatory
*   not shortened beyond recognition, but rather longer if it makes their meaning clearer

*Correct naming of variables:*

*   `$singletonObjectsRegistry`
*   `$argumentsArray`
*   `$aLotOfHTMLCode`

*Incorrect naming of variables*

*   `$sObjRgstry`
*   `$argArr`
*   `$cx`

As a special exception you may use variable names like $i, $j and $k for numeric indexes in for loops if it's clear what they mean on the first sight. But even then you should want to avoid them.

### Constant Names

All constant names are written in UPPERCASE. This includes TRUE, FALSE and NULL. Words can be separated by underscores - you can also use the underscore to group constants thematically:

*   STUFF_LEVEL
*   COOLNESS_FACTOR
*   PATTERN\_MATCH\_EMAILADDRESS
*   PATTERN\_MATCH\_VALIDHTMLTAGS

It is, by the way, a good idea to use constants for defining regular expression patterns (as seen above) instead of defining them somewhere in your code.

### Filenames

These are the rules for naming files:

*   All filenames are UpperCamelCase.
*   Class and interface files are named according to the class or interface they represent
*   Each file must contain only one class or interface
*   Names of files containing code for unit tests must be the same as the class which is tested, appended with "Test.php".
*   Files are placed in a directory structure representing the namespace structure.

*File naming in SMPL*

SMPL.TemplateEngine/Classes/SMPL/TemplateEngine/TemplateEngineInterface.php

Contains the interface \SMPL\TemplateEngine\TemplateEngineInterface which is part of the package *SMPL.TemplateEngine*

SMPL.Core/Classes/SMPL/Core/Error/RuntimeException.php

Contains the \SMPL\Core\Error\RuntimeException being a part of the package *SMPL.Core*

Acme.DataAccess/Classes/Acme/DataAccess/Manager.php

Contains class \Acme\DataAccess\Manager which is part of the package *Acme.DataAccess*

SMPL.Core/Tests/Unit/Package/PackageManagerTest.php

Contains the class \SMPL\Core\\Tests\Unit\Package\PackageManagerTest which is a PHPUnit testcase for Package\PackageManager.

## PHP Code Formatting

### Inline comments

Inline comments must be indented one level more than surrounding source code.

### Strings

In general, we use single quotes to enclose literal strings:

    $vision = 'Inspiring people to share';
    

If you'd like to insert values from variables, concatenate strings:

    $message = 'Hey ' . $name . ', you look ' . $look . ' today!';
    

A space must be inserted before and after the dot for better readability:

    $vision = 'Inspiring people' . 'to share';
    

You may break a string into multiple lines if you use the dot operator. You'll have to indent each following line to mark them as part of the value assignment:

    $vision = 'Inspiring' .
      'people ' .
      'to ' .
      'share';
    

### Arrays

### Classes

### Functions and Methods

### if Statements

*   There needs to be one space between the if keyword and the opening brace "(" of the test expression
*   After the closing brace ")" of the test expression follows one space before the curly brace "{"
*   else and elseif are on the same line as their corresponding curly braces

*if statements*:

    if ($something || $somethingElse) {
      doThis();
    } else {
      doSomethingElse();
    }
    
    if (weHaveALotOfCriteria() === TRUE
      && notEverythingFitsIntoOneLine() === TRUE
      || youJustTendToLikeIt() === TRUE) {
         doThis();
    
    } else {
      ...
    }
    

### switch Statements

*   There needs to be one space between the if keyword and the opening brace "(" of the test expression
*   After the closing brace ")" of the test expression follows one space before the curly brace "{"
*   break is indented to the same level as case keywords

*switch statements*:

    switch ($something) {
      case FOO:
         $this->handleFoo();
      break;
      case BAR:
         $this->handleBar();
      break;
      default:
         $this->handleDefault();
    }
    

## Development Process

### Commit Messages

To have a clear and focused history of code changes is greatly helped by using a consistent way of writing commit messages. Because of this and to help with (partly) automated generation of change logs for each release we have defined a fixed syntax for commit messages that is to be used.

Tip

Never commit without a commit message explaining the commit!

The syntax is as follows:

*   One line with a short summary, no full stop at the end. If the change breaks things on the user side, start the line with **[!!!]**. This indicates a breaking change that needs human action when updating.
    
    Then followed by one or more of the following codes:
    
    [FEATURE]
    
    A feature change. Most likely it will be an added feature, but it could also be removed. For additions there should be a corresponding ticket in the issue tracker.
    
    [BUGFIX]
    
    A fix for a bug. There should be a ticket corresponding to this in the issue tracker as well as a new) unit test for the fix.
    
    [API]
    
    An API change, that is methods have been added or removed; method signatures or return types have changed. This only refers to the public API, i.e. methods tagged with @api.
    
    [CONFIGURATION]
    
    Some configuration change. That could be a changed default value, a new setting or the removal of some setting that used to exist.
    
    [TASK]
    
    Anything not covered by the above categories, e.g. coding style cleanup. Usually only used if there's no corresponding ticket.

*   Then follows (after a blank line) a custom message explaining what was done. It should be written in a style that serves well for a change log read by users. In case of breaking changes give a hint on what needs to be changed by the user.

*   If corresponding tickets exist, mention the ticket numbers using footer lines after another blank line and use the following actions:
    
    Fixes: #<number>
    
    If the change fixes a bug.
    
    Resolves: #<number>
    
    If the change resolves a feature request or task.
    
    Related: #<number>
    
    If the change relates to an issue but does not resolve or fix it.

*   Fixes may be targeted at not only the master branch (i.e. the next major/point release), but also for a point release in an older branch. Thus a Releases footer must address the target branches.

*A commit messages following the rules...*:

    [TASK] Short (50 chars or less) summary of changes
    
    More detailed explanatory text, if necessary.  Wrap it to about 72
    characters or so.  In some contexts, the first line is treated as the
    subject of an email and the rest of the text as the body.  The blank
    line separating the summary from the body is critical (unless you omit
    the body entirely); tools like rebase can get confused if you run the
    two together.
    
    Write your commit message in the present tense: "Fix bug" and not "Fixed
    bug."  This convention matches up with commit messages generated by
    commands like git merge and git revert.
    
    Further paragraphs come after blank lines.
    
    * Bullet points are okay, too
    * An asterisk is used for the bullet, it can be preceded by a single
      space. This format is rendered correctly by Forge (redmine)
    * Use a hanging indent
    
    Resolves: #123
    Resolves: #456
    Related: #789
    Releases: 1.0, 1.1
    

### Source Code Documentation

All code must be documented using PHPDoc. The syntax is similar to that known from the Java programming language (JavaDoc). This way code documentation can automatically be generated using [PHP_UML][4].

### Documentation Blocks

A file contains different documentation blocks, relating to the class in the file and the members of the class. A documentation block is always used for the entity it precedes.

### Class Documentation

Classes have their own documentation block describing the classes purpose.

*Standard documentation block*:

    /**
     * First sentence is short description. Then you can write more, just as you like
     *
     * Here may follow some detailed description about what the class is for.
     *
     * Paragraphs are separated by an empty line.
     */
    class SomeClass {
     ...
    }
    

Additional tags or annotations, such as @see or @Core\Aspect, can be added as needed.

### Documenting Variables, Constants, Includes

Properties of a class should be documented as well. We use the short version for documenting them.

*Standard variable documentation block*:

    /**
     * A short description, very much recommended
     * @var string
     */
    protected $title = 'Untitled';
    

### Method Documentation

For a method, at least all parameters and the return value must be documented. The @access tag must not be used as it makes no sense (we're using PHP 5 for a reason, don't we?)

*Standard method documentation block*:

    /**
     * A description for this method
     *
     * Paragraphs are separated by an empty line.
     *
     * @param \SMPL\Blog\Domain\Model\Post $post A post
     * @param string $someString This parameter should contain some string
     * @return void
     */
    public function addStringToPost(\SMPL\Blog\Domain\Model\Post $post, $someString) {
     ...
    }
    

A special note about the @param tags: The parameter type and name are separated by one space, not aligned. Do not put a colon after the parameter name. Always document the return type, even if it is void - that way it is clearly visible it hasn't just been forgotten (only constructors never have a @return annotation!).

### Defining the Public API

Not all methods with a public visibility are necessarily part of the intended public API of a project. For SMPL, only the methods explicitly defined as part of the public API will be kept stable and are intended for use by developers using SMPL. Also the API documentation we produce will only cover the public API.

To mark a method as part of the public API, include an @api annotation for it in the docblock.

*Defining the public API*:

    /**
     * This method is part of the public API.
     *
     * @return void
     * @api
     */
    public function fooBar() {
     ...
    }
    

__*Tip:*__

> When something in a class or an interface is annotated with @api make sure to also annotate the class or interface itself! Otherwise it will be ignored completely when official API documentation is rendered!

### Overview of Documentation Annotations

There are not only documentation annotations that can be used. In SMPL annotations are also used in the MVC component, for defining aspects and advices for the AOP framework as well as for giving instructions to the Persistence framework. See the individual chapters for information on their purpose and use.

Here is a list of annotations used within the project. They are grouped by use case and the order given here should be kept for the sake of consistency.

__Interface Documentation__

*   @api
*   @since
*   @deprecated

__Class Documentation__

*   @api
*   @since
*   @deprecated
*   @CoreEntity
*   @CoreValueObject
*   @CoreScope
*   @CoreAspect

__Property Documentation__

*   @var
*   @CoreIntroduce
*   @CoreIdentity
*   @CoreTransient
*   @CoreLazy
*   @api
*   @since
*   @deprecated

__Constructor Documentation__

*   @param
*   @throws
*   @api
*   @since
*   @deprecated

__Method Documentation__

*   @param
*   @return
*   @throws
*   @CoreValidate
*   @CoreIgnoreValidation
*   @CoreSignal
*   @api
*   @since
*   @deprecated
*   @CorePointcut
*   @CoreAfterReturning
*   @CoreAfterThrowing
*   @CoreAround
*   @CoreBefore


__*Tip*__
> Additional annotations (more or less only the @todo and @see come to mind here), should be placed after all other annotations.

## Best Practices

### SMPL

This section gives you an overview of SMPL's coding rules and best practices.

### Error Handling and Exceptions

SMPL makes use of a hierarchy for its exception classes. The general rule is to throw preferably specific exceptions and usually let them bubble up until a place where more general exceptions are caught. Consider the following example:

Some method tried to retrieve an object from the object manager. However, instead of providing a string containing the object name, the method passed an object (of course not on purpose - something went wrong). The object manager now throws an InvalidObjectName exception. In order to catch this exception you can, of course, catch it specifically - or only consider a more general Object exception (or an even more general Core exception). This all works because we have the following hierarchy:

    + \SMPL\Core\Exception
    + \SMPL\Core\Object\Exception
    + \SMPL\Core\Object\Exception\InvalidObjectNameException
    

### Throwing an exception

When throwing an exception, make sure to provide a clear error message and an *error code being the unix timestamp of when you write the \``throw\`` statement*. That error code must be unique, so watch out when doing copy and paste!

For every exception there should be a page on the SMPL wiki, as exception messages link to that page, identified by the error code (unix timestamp).

### PHP in General

*   All code should be object oriented. This means there should be no functions outside classes if not absolutely necessary. If you need a "container" for some helper methods, consider creating a static class.

*   All code must make use of PHP5 advanced features for object oriented programming.
    
    *   Use [PHP namespaces][5]
    *   Always declare the scope (public, protected, private) of methods and member variables
    *   Make use of iterators and exceptions, have a look at the [SPL][6]
*   Make use of [type-hinting][7] wherever possible

*   Always use <?php as opening tags (never only <?)

*   Always use the closing tag ?> at the end of a file, don't leave it out (this ain't no Zend Framework, dude)

*   Never use the shut-up operator @ to suppress error messages. It makes debugging harder, very slow, and is generally bad practice.

*   Prefer strict comparisons whenever possible, to avoid problems with truthy and falsy values that might behave different than what you expect. Here are some examples:

        if ($template)             // BAD
        if (isset($template))      // GOOD
        if ($template !== NULL))   // GOOD
        if ($template !== ''))     // GOOD
        
        if (strlen($template) > ) // BAD! strlen("-1") is greater than 0
        if (is_string($template) && strlen($template) > ) // BETTER
        
        if ($foo == $bar)          // BAD, avoid truthy comparisons
        if ($foo != $bar)          // BAD, avoid falsy comparisons
        if ($foo === $bar))        // GOOD
        if ($foo !== $bar))        // GOOD
        

*   Order of methods in classes. To gain a better overview, it helps if methods in classes are always ordered in a certain way. We prefer the following:
    
    *   constructor
    *   injection methods
    *   initialization methods (including initializeObject())
    *   public methods
    *   protected methods
    *   private methods
    *   shutdown methods
    *   destructor

*   Avoid double-negation in method parameters. Instead of exportSystemView(..., $noRecurse) use exportSystemView(..., $recurse). It is more logical to pass TRUE if you want recursion instead of having to pass FALSE. In general, parameters negating things are a bad idea.

### Comments

In general, comments are a good thing and we strive for creating a well-documented source code. However, inline comments can often be a sign for a bad code structure or method naming. As an example, consider the example for a coding smell:

    // We only allow valid persons
    if (is_object($p) && strlen($p->lastN) >  && $p->hidden === FALSE && ↩
     $this->environment->moonPhase === MOON_LIB::CRESCENT) {
     $xmM = $thd;
    }
    

This is a perfect case for the refactoring technique "extract method": In order to avoid the comment, create a new method which is as explanatory as the comment:

    if ($this->isValidPerson($person) {
      $xmM = $thd;
    }
    

Bottom line is: You may (and are encouraged to) use inline comments if they support the readability of your code. But always be aware of possible design flaws you probably try to hide with them.

 [1]: http://docs.typo3.org/flow/TYPO3FlowDocumentation/stable/TheDefinitiveGuide/PartV/CodingGuideLines/PHP.html
 [2]: http://www.phpdoc.org/docs/latest/for-users/phpdoc-reference.html
 [3]: http://discuss.fogcreek.com/joelonsoftware/default.asp?cmd=show&ixPost=3978
 [4]: http://pear.php.net/package/PHP_UML
 [5]: http://www.php.net/manual/language.namespaces.php
 [6]: http://www.php.net/manual/ref.spl.php
 [7]: http://www.php.net/manual/language.oop5.typehinting.php
