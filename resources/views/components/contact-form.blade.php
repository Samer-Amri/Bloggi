<div class="contact-form-wrap">
    <h2 class="contact__title">Get in touch</h2>
    <p>Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. </p>
    <form id="contact-form" action="{{ route('frontend.do_contact') }}" method="post">
        @csrf
        <div class="single-contact-form">
            <input type="text" name="name" placeholder="Name" value="{{ old('name') }}">
            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="single-contact-form space-between">
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}">
            <input type="text" name="mobile" placeholder="Mobile" value="{{ old('mobile') }}">
            @error('email')<span class="text-danger">{{ $message }}</span>@enderror
            @error('mobile')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="single-contact-form">
            <input type="text" name="title" placeholder="Subject" value="{{ old('title') }}">
            @error('title')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="single-contact-form message">
            <textarea name="message" placeholder="Type your message here..">{{ old('message') }}</textarea>
            @error('message')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="contact-btn">
            <button type="submit">Send Message</button>
        </div>
    </form>
</div>
