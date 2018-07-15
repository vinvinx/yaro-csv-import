# Yaro CSV Import

## Description
Plugin for importing data from a csv file.

The first version of the plug-in will receive data from the csv file to add or update records of the page type.
The main functions of the plugin:
1. create new pages
2. update existing pages
3. Add a thumbnail of the page
4. Add and update custom fields

## Installation

1. Upload `yaro-csv-import` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions


## Changelog

= 1.0.0 =
```
- Create the initial structure of the plug-in
- Add a plug-in page in the administrative section.
  - The page should consist of three tabs:
    - import tab
    - settings tab
    - documentation tab
- Add functionality to load and parse the csv file.
- Add the functionality of adding, updating pages.
- Add the functionality of adding, updating a thumbnail page.
- Add the functionality of adding, updating the custom fields of the page.
```
## Csv file structure

To import, you must use `UTF-8` encoding. `Excel` stores in csv with some kind of its encoding. Therefore, after saving to csv, the file must be converted to `UTF-8` encoding, you can do this for example in `Notepade++`
```
`ID`                                Unique page idifier.
`post_parent`                       Parent page title.
`wp_page_template`                  The name of the php file responsible for the page template.
                                    At the moment, there are the following templates: 
                                        `tml-device-page.php`, 
                                        `tml-model-page.php`, 
                                        `tml-vendor-page.php`
`post_title`                        The title of the page. **Required** field. 
                                    On it occurs and identification of records at the first import and if ID is not specified.
`post_name`                         Page slag.
`thumbnail_image`                   Link to the thumbnail of the page.
                                    It is assumed that the images are already loaded into the folder.
                                    Link to uploads directory.
`post_status`                       Page status - (`publish`, `draft`, `trash`) If the value is not set, 
                                    only the custom fields of the post are updated.
`menu_order`                        Sort number in the menu or on the parent page
`comment_status`                    Status of comments (allowed - 1, forbidden - 0)
`post_content`                      Page content
`custom-multiple_services_type`     Type of service, for the filter
`custom-multiple_services_name`     Service name
`custom-multiple_services_time`     Service execution time
`custom-multiple_services_price`    Price of the service
```
All custom fields must begin with the prefix `custom`. In the future, the prefixes will be configured in the plugin.
The second word in the field names, defines a custom field in which the combined data will be written.
The data will be recorded in `JSON` format.
```
`custom-multiple`   The prefix is used to specify a multiple value
```
