# spec262 - ECMAScript specification supervisor

## Note

THIS SOFTWARE IS A WORK IN PROGRESS. DO NOT USE!

## Usage

```SHELL
$ spec262 ./src
```

spec262 will traverse the given directory and search for specification links preceeding code-structures like functions, methods, classes.
If an existing link was found, the specification defined algorithms will be overlayed over the given single-line-comments in the implementation to catch specification changes.

This workflow can be used to upgrade from one specification version to another.

## License

All software contained in this package is released under a [BSD 3-Clause License](https://raw.githubusercontent.com/oruborus/spec262/master/LICENSE).
