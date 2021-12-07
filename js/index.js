assignmentInfoArray = [
    poster = {
        description: 'A movie poster created using photoshop',
        link: `img/condor-heroes.jpg`,
    },
    sortingHat = {
        description: 'An app for Harry Potter fan to be sorted into one of the Hogwart houses. This app used the Harry Potter API. This app used jquery.',
        link: `https://hlai16.github.io/sortingHatCeremonyByJoanLai/`,
    },
    chart = {
        description: 'An article on cultured meat written by me. I used js chart plugins to show some of the data mentioned in the article. The overall layout is from bootstraps.',
        link: `https://hlai16.github.io/culturedMeatEssay/`,
    },
    wideEye = {
        description: 'A live wordpress site I created for a paralegal client. Client chooses the colour theme. Logo is modified by me and at client\'s satisfaction',
        link: `https://wideeyeparalegal.com`,
    },
    moribus = {
        description: 'A react webpage that uses axios to load and filter the vegan makeups for clients to browse. Moribus also incorporated firebase to let clients posted their comments about the products',
        link: `https://ethicalmkp.github.io/Moribus/`,
    },
    cravings = {
        description: 'An API call website that lets users browse the top 10 restaurants of their cravings.',
        link: `https://cravingsbyarmanandgloria.github.io/armanAlianiGloriaLaiProjectFour/`,
    },
]



const joansOffice = {};

// credit to Julio Codes: https://www.youtube.com/watch?v=sJ1uvHIJKTY
joansOffice.scrollFadeIn = $(document).on('scroll', function () {
    const headerHeight = $('.heroDiv').height();
    let about = $('.about');

    $(window).scroll(function () {
        if ($(this).scrollTop() > headerHeight) {
            $(about).addClass('visible');
        } else {
            $(about).removeClass('visible');
        }

    })

    // credit to Julio Codes: https://www.youtube.com/watch?v=sJ1uvHIJKTY
    // was making a 'misbehaving' navBar when attempting to do it myself with what I learned from Adolfsson on Codepen.
    let navBar = $('.mainNavBar');
    const headerAndAboutHeight = $('header').height();

    $(window).scroll(function () {
        if ($(this).scrollTop() > headerAndAboutHeight) {
            $(navBar).addClass('fixed');
            $(navBar).removeClass('hidden');
        } else {
            $(navBar).removeClass('fixed');
            $(navBar).addClass('hidden');
        }
    })
    //media query size to 900px:
    let mobileBar = $('.mobileContainer');
    $(window).scroll(function () {
        if ($(this).scrollTop() > headerAndAboutHeight) {
            $(mobileBar).addClass('fixed');
            $(mobileBar).removeClass('hidden');
        } else {
            $(mobileBar).removeClass('fixed');
            $(mobileBar).addClass('hidden');
        }
    })
});


joansOffice.hamburgerOn = () => {
    $('.icon').on( 'tap touchstart', (e) => {
        e.preventDefault();
        $('.myLinks').toggle('show');
    })
}


// SLICK PLUGIN
joansOffice.slick = $(document).ready(function () {
    $('.myAssignments').slick({
        accessibility: true,
        adaptiveHeight: false,
        arrows: true,
        prevArrow: '<button type="button" data-role="none" class="slick-prev">Previous</button>',
        nextArrow: '<button type="button" data-role="none" class="slick-next">Next</button>',
        autoplay: false,
        cssEase: 'ease',
        customPaging: function (slider, i) {
            return '<button type="button" data-role="none">' + (i + 1) + '</button>';
        },
        dots: true,
        dotsClass: 'slick-dots',
        draggable: true,
        easing: 'linear',
        edgeFriction: 0.35,
        infinite: true,
        mobileFirst: true,
        pauseOnHover: true,
        pauseOnFocus: true,
        pauseOnDotsHover: false,
        respondTo: 'mobile',
        speed: 1000,
        zIndex: 1000
    });
});

joansOffice.slideClickShowsInfo = () => {
    $('.slide1').on('click tap touchstart', () => {
        $('.assignmentInfo').html(
            `<div class="slideCover animate__animated animate__pulse">
                <p>${assignmentInfoArray[0].description}</p>
                <p>Link: <a href="${assignmentInfoArray[0].link}">Click Here</a></p>
                </div>`
        )
    })
    $('.slide2').on('click tap touchstart', () => {
        $('.assignmentInfo').html(
            `<div class="slideCover animate__animated animate__pulse">
                <p>${assignmentInfoArray[1].description}</p>
                <p>Link: <a href="${assignmentInfoArray[1].link}">Click Here</a></p>
                </div>`
        )
    })
    $('.slide3').on('click tap touchstart', () => {
        $('.assignmentInfo').html(
            `<div class="slideCover animate__animated animate__pulse">
                <p>${assignmentInfoArray[2].description}</p>
                <p>Link: <a href="${assignmentInfoArray[2].link}">Click Here</a></p>
                </div>`
        )
    })
    $('.slide4').on('click tap touchstart', () => {
        $('.assignmentInfo').html(
            `<div class="slideCover animate__animated animate__pulse">
                <p>${assignmentInfoArray[3].description}</p>
                <p>Link: <a href="${assignmentInfoArray[3].link}">Click Here</a></p>
                </div>`
        )
    })
    $('.slide5').on('click tap touchstart', () => {
        $('.assignmentInfo').html(
            `<div class="slideCover animate__animated animate__pulse">
                <p>${assignmentInfoArray[4].description}</p>
                <p>Link: <a href="${assignmentInfoArray[4].link}">Click Here</a></p>
                </div>`
        )
    })
    $('.slide6').on('click tap touchstart', () => {
        $('.assignmentInfo').html(
            `<div class="slideCover animate__animated animate__pulse">
                <p>${assignmentInfoArray[5].description}</p>
                <p>Link: <a href="${assignmentInfoArray[5].link}">Click Here</a></p>
                </div>`
        )
    })
}


//init

joansOffice.init = () => {
    joansOffice.hamburgerOn();
    joansOffice.slideClickShowsInfo();
}

//doc ready
$(function () {
    $('.rippleEffect').ripples({
        imageUrl: null,
        resolution: 256,
        dropRadius: 20,
        perturbance: 0.02,
        interactive: true
    });
    joansOffice.init();
})
