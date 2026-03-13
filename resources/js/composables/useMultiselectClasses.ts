/**
 * Tailwind CSS classes for @vueform/multiselect, themed to match the
 * project's navy / warm-yellow design system (CSS variables via bg-primary, etc.).
 */
export const multiselectClasses = {
    container:
        'relative mx-auto w-full flex items-center justify-end box-border cursor-pointer border border-input rounded-md bg-background text-sm leading-snug outline-none',
    containerDisabled: 'cursor-default bg-muted opacity-60',
    containerOpen: 'rounded-b-none',
    containerOpenTop: 'rounded-t-none',
    containerActive: 'ring-1 ring-ring',
    wrapper:
        'relative mx-auto w-full flex items-center justify-end box-border cursor-pointer outline-none',
    singleLabel:
        'flex items-center h-full max-w-full absolute left-0 top-0 pointer-events-none bg-transparent leading-snug pl-3 pr-16 box-border',
    singleLabelText: 'overflow-ellipsis overflow-hidden block whitespace-nowrap max-w-full',
    multipleLabel:
        'flex items-center h-full absolute left-0 top-0 pointer-events-none bg-transparent leading-snug pl-3',
    search:
        'w-full absolute inset-0 outline-none focus:ring-0 appearance-none box-border border-0 text-sm font-sans bg-background rounded-md pl-3',
    tags: 'flex-grow flex-shrink flex flex-wrap items-center mt-1 pl-2 min-w-0',
    tag: 'bg-primary text-primary-foreground text-xs font-semibold py-0.5 pl-2 rounded mr-1 mb-1 flex items-center whitespace-nowrap min-w-0',
    tagDisabled: 'pr-2 opacity-50',
    tagWrapper: 'whitespace-nowrap overflow-hidden overflow-ellipsis',
    tagWrapperBreak: 'whitespace-normal break-all',
    tagRemove:
        'flex items-center justify-center p-1 mx-0.5 rounded-sm hover:bg-black/10 group',
    tagRemoveIcon:
        'bg-multiselect-remove bg-center bg-no-repeat opacity-30 inline-block w-3 h-3 group-hover:opacity-60',
    tagsSearchWrapper: 'inline-block relative mx-1 mb-1 flex-grow flex-shrink h-full',
    tagsSearch:
        'absolute inset-0 border-0 outline-none focus:ring-0 appearance-none p-0 text-sm font-sans box-border w-full bg-transparent',
    tagsSearchCopy: 'invisible whitespace-pre-wrap inline-block h-px',
    placeholder:
        'flex items-center h-full absolute left-0 top-0 pointer-events-none bg-transparent leading-snug pl-3 text-muted-foreground',
    caret:
        'bg-multiselect-caret bg-center bg-no-repeat w-2.5 h-4 py-px box-content mr-3 relative z-10 opacity-40 flex-shrink-0 flex-grow-0 transition-transform transform pointer-events-none',
    caretOpen: 'rotate-180 pointer-events-auto',
    clear:
        'pr-3 relative z-10 opacity-40 transition duration-300 flex-shrink-0 flex-grow-0 flex hover:opacity-80',
    clearIcon:
        'bg-multiselect-remove bg-center bg-no-repeat w-2.5 h-4 py-px box-content inline-block',
    spinner:
        'bg-multiselect-spinner bg-center bg-no-repeat w-4 h-4 z-10 mr-3 animate-spin flex-shrink-0 flex-grow-0',
    infinite: 'flex items-center justify-center w-full',
    infiniteSpinner:
        'bg-multiselect-spinner bg-center bg-no-repeat w-4 h-4 z-10 animate-spin flex-shrink-0 flex-grow-0 m-3.5',
    dropdown:
        'max-h-60 absolute -left-px -right-px bottom-0 transform translate-y-full border border-input -mt-px overflow-y-scroll z-50 bg-background flex flex-col rounded-b',
    dropdownTop: '-translate-y-full top-px bottom-auto rounded-b-none rounded-t',
    dropdownHidden: 'hidden',
    options: 'flex flex-col p-0 m-0 list-none',
    optionsTop: '',
    group: 'p-0 m-0',
    groupLabel:
        'flex text-sm box-border items-center justify-start text-left py-1 px-3 font-semibold bg-muted cursor-default leading-normal',
    groupLabelPointable: 'cursor-pointer',
    groupLabelPointed: 'bg-muted text-foreground',
    groupLabelSelected: 'bg-primary text-primary-foreground',
    groupLabelDisabled: 'bg-muted text-muted-foreground cursor-not-allowed',
    groupLabelSelectedPointed: 'bg-primary text-primary-foreground opacity-90',
    groupLabelSelectedDisabled:
        'text-primary-foreground/50 bg-primary/50 cursor-not-allowed',
    groupOptions: 'p-0 m-0',
    option:
        'flex items-center justify-start box-border text-left cursor-pointer text-sm leading-snug py-2 px-3',
    optionPointed: 'text-foreground bg-muted',
    optionSelected: 'text-primary-foreground bg-primary',
    optionDisabled: 'text-muted-foreground cursor-not-allowed',
    optionSelectedPointed: 'text-primary-foreground bg-primary opacity-90',
    optionSelectedDisabled: 'text-primary-foreground/50 bg-primary/50 cursor-not-allowed',
    noOptions: 'py-2 px-3 text-muted-foreground bg-background text-left',
    noResults: 'py-2 px-3 text-muted-foreground bg-background text-left',
    fakeInput:
        'bg-transparent absolute left-0 right-0 -bottom-px w-full h-px border-0 p-0 appearance-none outline-none text-transparent',
    assist: 'absolute -m-px w-px h-px overflow-hidden',
    spacer: 'h-9 py-px box-content',
};
