// JavaScript Document
function Munge(mung, string){
    string = string.value.toLowerCase();
    var c = [/[\xC0-\xC6]/g,/[\xE0-\xE6]/g,/[\xC8-\xCB]/g,/[\xE8-\xEB]/g,/[\xCC-\xCF]/g,/[\xEC-\xEF]/g,/[\xD2-\xD6]/g,/[\xF2-\xF6]/g,/[\xD9-\xDC]/g,/[\xF9-\xFC]/g,/(\x9F|xDD|\xFD|\xFF)/g,/(\xC7|\xE7)/g,/(\xD1|\xF1)/g,/(\x8A|\x9A)/g,/(\x8E|\x9E)/g],
    var d = ['a','a','e','e','i','i','o','o','u','u','y','c','n','s','z'];
    var i;
    for(i in c)
    {
        string = string.replace(c[i],d[i]);
    }
    
    string = string.replace(/[^A-Za-z0-9 \-]+/g,'').replace(/(\s|\-)+/g,'-').replace(/^-+|-+$/g,'');
    mung.value = string;
}
