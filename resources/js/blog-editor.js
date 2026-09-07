import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { TextStyle, Color } from '@tiptap/extension-text-style';
import Highlight from '@tiptap/extension-highlight';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import Youtube from '@tiptap/extension-youtube';
import TextAlign from '@tiptap/extension-text-align';
import FileHandler from '@tiptap/extension-file-handler';
import { Node, mergeAttributes } from '@tiptap/core';

const CtaButton = Node.create({
    name: 'ctaButton',
    group: 'inline',
    inline: true,
    atom: true,
    addAttributes() {
        return { href: { default: '#' }, label: { default: 'Click Here' } };
    },
    parseHTML() { return [{ tag: 'a[data-cta-button]' }]; },
    renderHTML({ HTMLAttributes }) {
        return ['a', mergeAttributes(HTMLAttributes, {
            'data-cta-button': 'true',
            style: 'display:inline-block;background:#7C3AED;color:#fff;padding:10px 22px;border-radius:8px;font-weight:600;text-decoration:none;font-size:14px;',
        }), HTMLAttributes.label];
    },
});

window.initBlogEditor = function (elementId, initialContent, onUpdate, uploadImageFile, onUploadStart, onUploadEnd) {
    const el = document.getElementById(elementId);

    // Defensive guard: if an editor was already mounted here (e.g. from
    // an unexpected second init() call), destroy it cleanly first.
    // This makes it structurally impossible to ever have two ProseMirror
    // views stacked in the same container, regardless of what triggered
    // the re-init.
    if (el._tiptapInstance) {
        el._tiptapInstance.destroy();
        el.innerHTML = '';
    }

    const editor = new Editor({
        element: el,
        extensions: [
            // Link and Underline are bundled INSIDE StarterKit in v3 —
            // configured here via StarterKit.configure(), not as
            // separate extensions. StarterKit must be listed exactly
            // ONCE in this array.
            StarterKit.configure({
                link: { openOnClick: false },
            }),
            Image.configure({
                resize: {
                    enabled: true,
                    directions: ['left', 'right'],
                    minWidth: 100,
                    alwaysPreserveAspectRatio: true,
                },
            }),
            TextStyle,
            Color,
            Highlight.configure({ multicolor: true }),
            TaskList,
            TaskItem.configure({ nested: true }),
            Youtube.configure({ width: 640, height: 360 }),
            TextAlign.configure({ types: ['heading', 'paragraph', 'image'] }),
            // Official drag/paste handler — replaces the hand-rolled
            // editorProps.handleDrop/handlePaste from the previous
            // version. onDrop/onPaste fire with the raw File objects;
            // we upload each one and insert the real image once the
            // server responds with an optimized URL.
            FileHandler.configure({
                allowedMimeTypes: ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
                onDrop: async (currentEditor, files, pos) => {
                    for (const file of files) {
                        onUploadStart?.();
                        const url = await uploadImageFile(file);
                        currentEditor.chain().insertContentAt(pos, { type: 'image', attrs: { src: url } }).focus().run();
                        onUploadEnd?.();
                    }
                },
                onPaste: async (currentEditor, files) => {
                    for (const file of files) {
                        onUploadStart?.();
                        const url = await uploadImageFile(file);
                        currentEditor.chain().focus().setImage({ src: url }).run();
                        onUploadEnd?.();
                    }
                },
            }),
            CtaButton,
        ],
        content: initialContent || '',
        onUpdate: ({ editor }) => onUpdate(editor.getHTML()),
    });

    el._tiptapInstance = editor;

    return {
        editor,
        insertImage(url) { editor.chain().focus().setImage({ src: url }).run(); },
        insertCta(href, label) { editor.chain().focus().insertContent({ type: 'ctaButton', attrs: { href, label } }).run(); },
        insertYoutube(url) { editor.commands.setYoutubeVideo({ src: url }); },
        toggleBold() { editor.chain().focus().toggleBold().run(); },
        toggleItalic() { editor.chain().focus().toggleItalic().run(); },
        toggleUnderline() { editor.chain().focus().toggleUnderline().run(); },
        toggleStrike() { editor.chain().focus().toggleStrike().run(); },
        toggleHeading(level) { editor.chain().focus().toggleHeading({ level }).run(); },
        toggleBlockquote() { editor.chain().focus().toggleBlockquote().run(); },
        toggleCodeBlock() { editor.chain().focus().toggleCodeBlock().run(); },
        toggleBulletList() { editor.chain().focus().toggleBulletList().run(); },
        toggleOrderedList() { editor.chain().focus().toggleOrderedList().run(); },
        toggleTaskList() { editor.chain().focus().toggleTaskList().run(); },
        setHorizontalRule() { editor.chain().focus().setHorizontalRule().run(); },
        setColor(color) { editor.chain().focus().setColor(color).run(); },
        setHighlight(color) { editor.chain().focus().toggleHighlight({ color }).run(); },
        setLink(url) { editor.chain().focus().setLink({ href: url }).run(); },
        isActive(name, attrs) { return editor.isActive(name, attrs); },
    };
};