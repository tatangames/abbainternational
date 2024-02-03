/**
 * @license Copyright (c) 2014-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
import { Alignment } from '@ckeditor/ckeditor5-alignment';
import { Bold, Italic, Underline } from '@ckeditor/ckeditor5-basic-styles';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import type { EditorConfig } from '@ckeditor/ckeditor5-core';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { FontSize } from '@ckeditor/ckeditor5-font';
import { HorizontalLine } from '@ckeditor/ckeditor5-horizontal-line';
import { DataFilter, HtmlComment } from '@ckeditor/ckeditor5-html-support';
import { TextPartLanguage } from '@ckeditor/ckeditor5-language';
import { List, ListProperties } from '@ckeditor/ckeditor5-list';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { SpecialCharacters, SpecialCharactersLatin } from '@ckeditor/ckeditor5-special-characters';
import { Undo } from '@ckeditor/ckeditor5-undo';
declare class Editor extends ClassicEditor {
    static builtinPlugins: (typeof Alignment | typeof BlockQuote | typeof Bold | typeof DataFilter | typeof Essentials | typeof FontSize | typeof HorizontalLine | typeof HtmlComment | typeof Italic | typeof List | typeof ListProperties | typeof Paragraph | typeof SpecialCharacters | typeof SpecialCharactersLatin | typeof TextPartLanguage | typeof Underline | typeof Undo)[];
    static defaultConfig: EditorConfig;
}
export default Editor;