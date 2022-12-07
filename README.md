# phputils
Useful PHP classes, matching various purposes

# EnvReplacer

Replaces markers in selected files with matching environment variable values.

It's very useful into docker containers, when you pass a lot of environment variables to be inserted into local configuration files.

Markers must be compliant with the regular expression `/__[A-Z0-9_]{5,30}__/` and corresponding environment variable must be the same without the delimiters  (`__`)

## Example

Marker: `__MARKER__`  
Env: `MARKER`

## Usage

Sample file containing markers, e.g php.ini:

```ini
...
upload_max_filesize = __UPLOAD_MAX_FILESIZE__
max_file_uploads = __MAX_FILE_UPLOADS__
...
```

Setting environment variables:

```bash
export UPLOAD_MAX_FILESIZE=64M
export MAX_FILE_UPLOADS=20
```

PHP Script:

```php
use Legolabs\Utils\EnvReplacer\EnvReplacer;

$replacer = new EnvReplacer('/etc/php/8.1/apache/php.ini');
$replacer->apply();
```

Result in file:

```ini
upload_max_filesize = 64M
max_file_uploads = 20
```