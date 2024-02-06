/**
 * @license Copyright (c) 2014-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
import { Alignment } from '@ckeditor/ckeditor5-alignment';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Bold, Italic, Underline } from '@ckeditor/ckeditor5-basic-styles';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import type { EditorConfig } from '@ckeditor/ckeditor5-core';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { FontFamily, FontSize } from '@ckeditor/ckeditor5-font';
import { Heading } from '@ckeditor/ckeditor5-heading';
import { HorizontalLine } from '@ckeditor/ckeditor5-horizontal-line';
import { LegacyList } from '@ckeditor/ckeditor5-list';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { SpecialCharacters, SpecialCharactersArrows, SpecialCharactersEssentials, SpecialCharactersLatin, SpecialCharactersText } from '@ckeditor/ckeditor5-special-characters';
import { TextTransformation } from '@ckeditor/ckeditor5-typing';
import { Undo } from '@ckeditor/ckeditor5-undo';
declare class Editor extends ClassicEditor {
    static builtinPlugins: (typeof Alignment | typeof Autoformat | typeof BlockQuote | typeof Bold | typeof Essentials | typeof FontFamily | typeof FontSize | typeof Heading | typeof HorizontalLine | typeof Italic | typeof LegacyList | typeof Paragraph | typeof SpecialCharacters | typeof SpecialCharactersArrows | typeof SpecialCharactersEssentials | typeof SpecialCharactersLatin | typeof SpecialCharactersText | typeof TextTransformation | typeof Underline | typeof Undo)[];
    static defaultConfig: EditorConfig;
}
export default Editor;
