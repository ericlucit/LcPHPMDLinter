# The PHPMD (PHP Mess Detector) Linter for Arcanist and Phabricator

This is a lint engine for PHPMD

## Prerequisites
This requires `phpunit` and `arcanist` to be installed locally


## Installing

If you are running lcutils, you can install with `lc get lcphpmdlinter --destination=/usr/local/bin/` in the `/usr/local/bin` directory

All other users, use git clone to install into your preferred destination

## Configuring your Project

In your project, configure the following

**.arcconfig**
```
"load": [
        "/usr/local/bin/lcphpmdlinter/src"
    ]
```

**.arclint**
```
"lcphpmdlinter": {
      "type": "lcphpmdlinter",
      "include": "(\\.php$)"
    }
```

**phpmd.xml**
Define your rules, something like the following
```lang=xml
<?xml version="1.0"?>
<ruleset name="My first PHPMD rule set"
         xmlns="http://pmd.sf.net/ruleset/1.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0
                     http://pmd.sf.net/ruleset_xml_schema.xsd"
         xsi:noNamespaceSchemaLocation="
                     http://pmd.sf.net/ruleset_xml_schema.xsd">
    <description>
        Your Custom Ruleset
    </description>

    <rule ref="rulesets/unusedcode.xml" />
    <rule ref="rulesets/cleancode.xml/UndefinedVariable" />
    <rule ref="rulesets/design.xml/DevelopmentCodeFragment" />
    <rule ref="rulesets/naming.xml/ConstructorWithNameAsEnclosingClass" />


</ruleset>
```


## License
All source code is licensed under the [Apache 2.0 license](LICENSE), the same license as for the Arcanist project.

## Lucit
Lucit is the company behind Layout : The application that connects big-ticket inventory applications (Automotive, Ag, Rec, Real Estate) to digital billboards, in real-time.

We stream inventory - direct, in real-time to digital billboards, anywhere. https://lucit.cc

