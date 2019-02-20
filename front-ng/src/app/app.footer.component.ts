import {Component} from '@angular/core';

@Component({
    selector: 'app-footer',
    template: `
        <div class="footer clearfix">
        <span>Copyright Egie Ramdan, 2019</span>
        <div class="footer-links">
            <a href="https://www.instagram.com/egieramdan/" class="first">Profile</a>
            <span class="link-divider">|</span>
            <a href="#">About</a>
            <span class="link-divider">|</span>
            <a href="#">Privacy</a>
            <span class="link-divider">|</span>
            <a href="#">Contact</a>
            <span class="link-divider">|</span>
            <a href="#">Map</a>
        </div>
    </div>
    `
})
export class AppFooterComponent {

}
