## Introduction

This MODX Extra adds some nice development features to the MODX manager.

## Search and replace

The Extras menu contains a new entry modDevTools, which opens a separate manager
page. page opens. It allows you to search and replace text in the contents of
chunks and templates.

Enter the string you want to search for and click the "Search" button. Below the
search field there are checkboxes to limit the search to specific elements
(chunks and templates).

After a successful search, the code of all found elements is displayed, with the
search text highlighted. A replacement must be made for each found element
individually. For this purpose there are three buttons below the code block:
"Replace", "Replace all" and "Skip".

The "Replace" button replaces the search string at the position where the cursor
is located (dark highlighting) with the replacement string. The "Skip" button
moves the cursor to the next match. The "Replace All" button replaces all search
strings in the code of the current element.

There are also two other buttons on the right side below the code element:
"Edit" and "Quickedit". When you click the "Edit" button, the element's edit
page opens. Clicking the "Quickedit" button opens a modal window where you can
quickly edit the element.

## Edit linked elements

The editing page of chunks, snippets and templates contains some additional
tabs. These tabs allow you to edit the content of linked (used) elements on the
current page. The following element pages contain the tabs listed below.

### Chunk
- **Templates** - The code of templates in which the currently edited chunk is called. If you have installed Ace, you can get an assignment of chunks in the template code.
- **Chunks** - The code of chunks that are called in the currently edited chunk. Snippet parameters specified in the call and default snippet parameters related to chunks also appear here.
- **Snippets** - The code of snippets that are called in the currently edited chunk. Under the code of snippets you can expand the description of their default settings.
- **Resources** - List of resources that are included in the template or whose content is called in some way in this chunk. You can go to the edited resource, open it in the browser, quickly change the template or publishing status or delete it to the trash.

### Template
- **Chunks** - The code of chunks that are called in the currently edited template.
- **Snippets** - The code of snippets that are called in the currently edited template.
- **Resources** - A list of resources with the currently edited template.

### Snippet
- **Resources** - A list of resources where the currently edited snippet is called.

## Linked Breadcrumb Path

On the Edit Resources page, a linked breadcrumb path is inserted title below the
page title. You can click any section of this breadcrumb path to edit the
clicked resource. There are two system settings for changing parts of this
breadcrumb path.

## System Settings

modDevTools uses the following system settings in the `moddevtools` namespace
and  in an additional tab on the custom manager page:

Setting | Description | Default
------- | ----------- | -------
Breadcrumb Limit | Limit of displayed breadcrumb entries. | 3
Debug | Log debug information in the MODX error log. | No
Show Breadcrumb Context | Show the context key in the breadcrumb path. | No