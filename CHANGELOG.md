# Changelog

All notable changes to `laravel-viddler-upload` will be documented in this file.

### 3.1.6
- It will now throw an Exception when the file fails to upload

### 3.1.5
- Checks for resolved instead of finished to prevent errors

### 3.1.4
- Fires ViddlerError when an exception is thrown

### 3.1.3
- Fixed Exception not found error

### 3.1.2
- Properly handles file not found errors

### 3.1.1
- Round the encoding_progress

### 3.1.0
- Can now specify your own model to use

### 3.0.0
- Removed jobs, now you must manually tell it to convert, upload and check the video

### 2.2.1
- Fixed a number comparison issue

### 2.2.0
- Will now fire a ViddlerFinished Event when that status is changed to finished

### 2.1.0
- Can now check the status of a viddler video

### 2.0.0
- Removed callback as it is breaks everything

### 1.0.6
- Handles Exceptions properly

### 1.0.5
- Added a missing use statement

### 1.0.4
- Fixed issue with uploading the same file after the first attempt failed

### 1.0.3
- Fixed migrations not publishing again

### 1.0.2
- Fixed migrations not publishing

### 1.0.1
- Updated to use zenapply/php-viddler instead of viddler/phpviddler

### 1.0.0
- Initial release and connected with packagist
