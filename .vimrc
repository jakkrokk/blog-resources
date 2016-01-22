if 0 | endif

filetype off

if has('vim_starting')
	if &compatible
		set nocompatible

	endif
	set runtimepath+=~/.vim/bundle/neobundle.vim

endif

call neobundle#begin(expand('~/.vim/bundle/'))

NeoBundle 'Shougo/neobundle.vim'
NeoBundle 'Shougo/vimproc', {
\ 'build' : {
 \ 'windows' : 'make -f make_mingw32.mak',
 \ 'cygwin' : 'make -f make_cygwin.mak',
 \ 'mac' : 'make -f make_mac.mak',
 \ 'unix' : 'make -f make_unix.mak',
\ },
\ }
NeoBundle 'tomasr/molokai'
call neobundle#end()

filetype plugin indent off
filetype indent off
syntax on
NeoBundleCheck

set background=dark
colorscheme hybrid
set t_Co=256

set encoding=utf-8
set number
set tabstop=4
set showmatch
set list
set listchars=tab:>-,trail:.
set cursorline


inoremap {<Enter> {}<Left><CR><ESC><S-o>
inoremap [<Enter> []<Left><CR><ESC><S-o>
inoremap (<Enter> ()<Left><CR><ESC><S-o>


function! s:SID_PREFIX()
return matchstr(expand('<sfile>'), '<SNR>\d\+_\zeSID_PREFIX$')
endfunction
function! s:my_tabline()  "{{{
let s = ''
for i in range(1, tabpagenr('$'))
 let bufnrs = tabpagebuflist(i)
 let bufnr = bufnrs[tabpagewinnr(i) - 1]  " first window, first appears
 let no = i  " display 0-origin tabpagenr.
 let mod = getbufvar(bufnr, '&modified') ? '!' : ' '
 let title = fnamemodify(bufname(bufnr), ':t')
 let title = '[' . title . ']'
 let s .= '%'.i.'T'
 let s .= '%#' . (i == tabpagenr() ? 'TabLineSel' : 'TabLine') . '#'
 let s .= no . ':' . title
 let s .= mod
 let s .= '%#TabLineFill# '
endfor
let s .= '%#TabLineFill#%T%=%#TabLine#'
return s
endfunction "}}}
let &tabline = '%!'. s:SID_PREFIX() . 'my_tabline()'
set showtabline=2 " 常にタブラインを表示

nnoremap    [Tag]   <Nop>
nmap    t [Tag]
" Tab jump
for n in range(1, 9)
execute 'nnoremap <silent> [Tag]'.n  ':<C-u>tabnext'.n.'<CR>'
endfor
map <silent> [Tag]c :tablast <bar> tabnew<CR>
map <silent> [Tag]x :tabclose<CR>
map <silent> [Tag]n :tabnext<CR>
map <silent> [Tag]p :tabprevious<CR>
