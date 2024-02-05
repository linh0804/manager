var scrollToTopTimeout = null;
var lastScrollTop = window.scrollY || document.documentElement.scrollTop;

var topButtons = document.querySelector('#scroll');
topButtons.style.transform = 'rotate(180deg)';

topButtons.addEventListener('click', function () {
    var scroll = rotate = 0;

    if(topButtons.style.transform == 'rotate(180deg)') {
        scroll = document.documentElement.scrollHeight;
        rotate = 180;
    }  
    window.scroll({
        top: scroll,
        left: 0,
        behavior: "smooth"
    });
    topButtons.style.transform = 'rotate('+rotate+'deg)';
})

window.addEventListener('scroll', function () {
    const scrollTopPosition = window.scrollY || document.documentElement.scrollTop;

    if(topButtons.style.display == 'none') {
        topButtons.style.display = 'block';
    }

    if (scrollTopPosition > lastScrollTop) {
        topButtons.style.transform = 'rotate(180deg)';
    } else if (scrollTopPosition < lastScrollTop) {
        topButtons.style.transform = 'rotate(0deg)';
    }

    lastScrollTop = scrollTopPosition <= 0
        ? 0: scrollTopPosition;

    clearTimeout(scrollToTopTimeout)
    scrollToTopTimeout = setTimeout(() => {
        topButtons.style.display = 'none';
    }, 3000)
});


document.addEventListener('click', function(e) {
    var targetId = e.target.id;
    if (targetId === 'nav-menu' || targetId === 'menuOverlay' || (document.body.classList.contains('has-menu') && e.target.closest('#subCol a:not(.noPusher)'))) {
        e.preventDefault();
        document.body.classList.toggle('has-menu');
    }
});

var search = document.getElementById('input_search');
var search_form = document.getElementById('search_form');
var search_submit = document.getElementById('submit_search');

search_form.addEventListener("submit", function (event) {
    var data = new FormData();    
    data.append("search", search.value);
    var elementToRemove = document.getElementById("list_search");
    if(elementToRemove) {
        elementToRemove.remove();
    }
    fetch(search_form.getAttribute('action'), {
        method: "POST",
        body: data,
        cache: "no-cache"
    }).then(function (response) {
        if (response.status != 200) {
            alert("Lỗi kết nối!");
            return false;
        }                    
        return response.json();
    }).then((data) => {
        var result = '';
        console.log(data);
        if(data.total) {
            var divElement = document.createElement('div');
            divElement.id = "list_search";
            divElement.style.position = "absolute";
            divElement.style.width = (search.offsetWidth + search_submit.offsetWidth) + 'px';
            for(var i = 0; i < data.total;i++) {
                result = data.search[i] + '' + result;
            }
            divElement.innerHTML = '<ul class="list_file" style="max-height:200px;overflow-y: scroll;text-align:left">' + result +'</ul>';
            search_form.appendChild(divElement);
        }
    });
    
    event.preventDefault();
    return false;
})

document.addEventListener('click', function(e) {
    var targetId = e.target.id;
    if (targetId != 'list_search') {
        var elementToRemove = document.getElementById("list_search");
        if(elementToRemove) {
            elementToRemove.remove();
        }
    }
});