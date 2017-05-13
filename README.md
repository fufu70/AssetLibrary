# ReflectionClass
A basic reflection class for PHP. This class is designed to be a Common Class to be utilized as an aid for testing or getting access to private/public/static access to functions/variables inside of PHP.

## Usage

To include the ReflectionClass in your composer file add the repo directory into your repositories section in the composer file and add the name of the module into the require section of the composer file.

```
"repositories": {
    ...
    { 
      "type": "vcs", 
      "url": "https://github.com/fufu70/ReflectionClass.git"
    }
    ...
}

...

"require": {
    ...
    "fufu70/reflectionclass": "dev-master",
    ...
}
```
