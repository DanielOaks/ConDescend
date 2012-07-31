#!/usr/local/bin/python3
# ----------------------------------------------------------------------------
# "THE BEER-WARE LICENSE" (Revision 42):
# <danneh@danneh.net> wrote this file. As long as you retain this notice you
# can do whatever you want with this stuff. If we meet some day, and you think
# this stuff is worth it, you can buy me a beer in return Daniel Oakley
# ----------------------------------------------------------------------------
#
# ConDescend Generator Script
#
import os
import sys
import json
import urllib.request, urllib.parse, urllib.error

paths = {
    'template': 'template.html',
    'site': 'site.json', 
    'build_dir': '..', 
    'soundcloud_cache': 'soundcloud-cache.json'
}


# soundcloud widget
def soundcloud_widget(song_url, indent=0):
    if not os.path.exists(paths['soundcloud_cache']):
        sc_cache = {}
        f = open(paths['soundcloud_cache'], 'w', encoding='utf8')
        f.write(json.dumps(sc_cache, sort_keys=True, indent=4))
        f.close()

    else:
        f = open(paths['soundcloud_cache'], encoding='utf8')
        f_read = f.read().replace('\ufeff', '') # utf8 bom strip
        sc_cache = json.loads(f_read)
        f.close()

        if song_url in sc_cache:
            return sc_cache[song_url]

    url = 'http://soundcloud.com/oembed?'
    url += urllib.parse.urlencode({
        b'format' : 'xml',
        b'iframe' : 'false',
        b'url' : 'http://soundcloud.com/' + song_url, 
    })

    embed = urllib.request.urlopen(url)
    embed_result = embed.read().decode()
    embed.close()
    embed_result
    current_html = embed_result.split('<html>&lt;![CDATA[')[1].split(']]&gt;</html>')[0]
    current_html = current_html.replace('&lt;', '<').replace('&gt;', '>').replace('\t', '    ')

    widget_html = ''
    first = True
    for line in current_html.split('\n'):
        if first:
            first = False
        else:
            widget_html += ' ' * indent
        widget_html += line + '\n'

        if '</object>' in line.strip():
            break

    sc_cache[song_url] = widget_html

    f = open(paths['soundcloud_cache'], 'w', encoding='utf8')
    f.write(json.dumps(sc_cache, sort_keys=True, indent=4))
    f.close()

    return widget_html


# item generation class
class ItemHtmlGenerator():

    def __init__(self, default_pages, item, indent):
        self.default_pages = default_pages
        self.item = item
        self.indent = indent

    def indent_in(self, by=4):
        self.indent += by

    def indent_out(self, by=4):
        self.indent -= by

    def html(self):
        html = ''

        html += '<div class="item-div"'
        if 'soundcloud' in self.item['style']:
            html += ' style="background:#cfffca;"'
        elif 'blog' in self.item['style']:
            html += ' style="background:#cacfff;"'
        elif 'text' in self.item['style']:
            html += ' style="background:#dadada;"'
        html += '>\n'

        self.indent_in()

        if 'text' in self.item['style']:
            html += ' ' * self.indent
            html += '<div class="item_html"'
            icon = ''
            for page in self.item['pages']:
                if 'icon' in self.default_pages[page]:
                    icon = self.default_pages[page]['icon']
            if icon:
                html += ' style="background: url(\'' + icon + '\') 20px 18px no-repeat;"'
            html += '>\n'
            self.indent_in()

            html += ' ' * self.indent
            html += '<h2>' + self.item['name'] + '</h2>\n'

            if 'text' in self.item:
                html += ' ' * self.indent
                html += self.item['text'] + '<br />\n'

            if 'link' in self.item:
                html += ' ' * self.indent
                html += '<a class="item_html_a" href="' + self.item['link']
                html += '">' + self.item['link-text']
                html += '</a>\n'

            if 'soundcloud-url' in self.item:
                html += ' ' * self.indent
                
                widget_html = soundcloud_widget(self.item['soundcloud-url'], self.indent)

                html += widget_html
                html += '\n'

            self.indent_out()
            html += ' ' * self.indent
            html += '</div>\n'

        elif 'image' in self.item['style']:
            html += '<a class="item-image-full" style="height: ' + self.item['image-height']
            html += '; background: url(\''
            html += self.item['image']
            html += '\') no-repeat;" href="'
            html += self.item['link']
            html += '"></a>'
            html += '\n'

        else:
            html += self.item['name'] + '\n'

        self.indent_out()
        html += ' ' * self.indent
        html += '</div>'

        return html


# page generation

if not os.path.exists(paths['build_dir']):
    os.makedirs(paths['build_dir'])


if os.path.exists:
    f = open(paths['site'], encoding='utf8')
    f_read = f.read().replace('\ufeff', '') # utf8 bom strip
    site = json.loads(f_read)
    f.close()

f = open(paths['template'], encoding='utf8')
template = f.read().replace('\ufeff', '') # utf8 bom strip
f.close()


default_pages = {
    'index': {
        'title': 'Everything', 
        'nav': 'index', 
        'items': 'all'
    }, 
    '404': {
        'nav': 'none'
    }, 
    '403': {
        'nav': 'none'
    }
}
default_pages.update(site['pages'])

# setting up the nav menu
nav_menu = ''
for page in sorted(default_pages.keys()):
    if 'nav' not in default_pages[page]:
        default_pages[page]['nav'] = 'normal'

    if default_pages[page]['nav'] == 'index':
        nav_menu = '<a id="headera" href="."></a>' + nav_menu
    elif default_pages[page]['nav'] == 'normal':
        nav_menu += '\n                <a class="b" href="' + page.lower() + '">' + page + '</a>'

# page items
for page in default_pages:
    if 'items' not in default_pages[page]:
        default_pages[page]['items'] = 'normal'

# item numbering, for merging and such
for item in range(len(site['items'])):
    site['items'][item]['number'] = item

# assembling pages
for page in default_pages:

    page_template = template

    # title
    if 'title' in default_pages[page]:
        title = default_pages[page]['title']
    else:
        title = page

    page_template = page_template.replace('<!--{condesc:title}-->', title)

    # nav menu
    page_navmenu = nav_menu
    if default_pages[page]['nav'] == 'normal':
        page_navmenu = page_navmenu.replace('<a class="b" href="' + page.lower() + '">',
                                            '<a class="b be" href="' + page.lower() + '">')

    page_template = page_template.replace('<!--{condesc:navmenu}-->', page_navmenu)

    # items
    print(' -', page)

    page_items = {}
    if default_pages[page]['items'] == 'all':
        for item in site['items']:
            page_items[item['number']] = item
    else:
        for item in site['items']:
            if page in item['pages']:
                page_items[item['number']] = item

    page_items_html = ''
    first = True
    for item in sorted(page_items.keys()):
        if first:
            first = False
        else:
            page_items_html += '                    '

        page_items_html += ItemHtmlGenerator(default_pages, page_items[item], indent=20).html()

        page_items_html += '\n'
        print('    -', page_items[item]['name'])

    page_template = page_template.replace('<!--{condesc:items}-->', page_items_html)

    # output
    f = open(paths['build_dir'] + os.sep + page.lower() + '.html', 'w')
    f.write(page_template)
    f.close()
