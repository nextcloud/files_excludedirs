# Excludedirs

Exclude local directories and files from being included in Nextcloud

## Usage

You can configure the directories and files to be included by setting the config entry from the command line.

```
occ config:app:set --value '[".snapshot","anotherfolder", "pattern/*/match"]' files_excludedirs exclude
```

Any file or folder matching one of the configured rules will not be included in Nextcloud if
it exists on the filesystem and any user will be blocked from creating any file or folder matching
any of the rules.

## Installation

* Clone the repository (or download and uncompress it) into a new subdirectory under your nextcloud/apps directory called files\_excludedirs
* In the files\_excludedirs subdirectory run this command to install it: "composer install"
* Make sure the "Exclude directories" app inside nextcloud has been enabled, and you should be good to go!
