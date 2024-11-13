<header class="flex-row-reverse flex-md-row">
    <div class="d-flex col justify-content-end justify-content-md-start">
        <a id="nav-icon1" href="#my-menu">
            <span></span>
            <span></span>
        </a>
    </div>
    <nav id="my-menu">
        <ul>
            <li><a href="/"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/></svg>&nbsp;&nbsp;  Home</a></li>
            <li><a href="/wms"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-boxes" viewBox="0 0 16 16"><path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571zm1 3.134 2.75 1.571v-3.134L8.5 9.933zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567zm2.242-2.433V3.504L8.5 5.076V8.21zM7.5 8.21V5.076L4.75 3.504v3.134zM5.258 2.643 8 4.21l2.742-1.567L8 1.076zM15 9.933l-2.75 1.571v3.134L15 13.067zM3.75 14.638v-3.134L1 9.933v3.134z"/></svg>&nbsp;&nbsp;  WMS</a>
                <ul>
                    <li><span>NJ</span>
                        <ul>
                            <li><a href="/wms/nj/thornton">Thornton</a>
                                <ul>
                                    <li><a href="/wms/nj/thornton/receiving">Receiving</a></li>
                                    <li><a href="/wms/nj/thornton/inventory">Inventory</a></li>
                                </ul>
                            </li>
                            <li><span>18 Muller</span>
                                <ul>
                                    <li><a href="/wms/nj/18muller/receiving">Receiving</a></li>
                                    <li><a href="/wms/nj/18muller/inventory">Inventory</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><span>SC</span></li>
                </ul>
            </li>
            <li><a href="/logout"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-door-open" viewBox="0 0 16 16"><path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/><path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117M11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5M4 1.934V15h6V1.077z"/></svg>&nbsp;&nbsp;  LOGOUT</a></li>
        </ul>
    </nav>
    <div id="header-logo" class="d-flex col-10 col-md-4  justify-content-center">
        <a href="/"><img src="/main/images/portal-header-logo.png"/></a>
    </div>
    <div id="logout"class="d-none d-md-flex col justify-content-end">
      <a href="/logout"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-door-open" viewBox="0 0 16 16"><path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/><path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117M11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5M4 1.934V15h6V1.077z"/></svg>&nbsp; LOGOUT</a>
    </div>
</header>
<script>
var menu=new MmenuLight(document.querySelector("#my-menu"),"all");var navigator=menu.navigation({title:'Menu'});var drawer=menu.offcanvas({position:'left'});document.querySelector('a[href="#my-menu"]').addEventListener("click",(evnt)=>{evnt.preventDefault();drawer.open()})
</script>

 
