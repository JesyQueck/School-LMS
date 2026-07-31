<x-layouts.guest>
    <x-public.hero
        title="Welcome to Greenfield Academy"
        subtitle="Empowering students to achieve excellence through a nurturing environment, dedicated faculty, and a world-class curriculum."
        :primaryCta="['href' => '/admissions', 'label' => 'Apply Now']"
        :secondaryCta="['href' => '/about', 'label' => 'Learn More']"
    />

    <section class="bg-white dark:bg-dark-surface border-b border-neutral-200 dark:border-dark-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 lg:gap-8">
                <x-public.stat-card label="Students" value="500+" icon="users" />
                <x-public.stat-card label="Teachers" value="25+" icon="graduation-cap" />
                <x-public.stat-card label="Years" value="29" icon="school" />
                <x-public.stat-card label="Pass Rate" value="100%" icon="award" />
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Welcome to Greenfield Academy"
                subtitle="We are a prestigious private school dedicated to nurturing young minds through academic excellence, creative thinking, and strong moral values."
                align="center"
            />

            <div class="max-w-3xl mx-auto text-center">
                <p class="text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed mb-6">
                    At Greenfield Academy, we believe every child has unique potential waiting to be unlocked. Our experienced educators provide personalized attention in small class sizes, ensuring each student receives the guidance they need to thrive academically and personally.
                </p>
                <p class="text-lg text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    From our rigorous JSS curriculum to our vibrant extracurricular programs, we prepare students not just for examinations, but for life beyond the classroom.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-dark-surface py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Why Choose Greenfield Academy"
                subtitle="We provide an exceptional educational experience that goes beyond textbooks."
                align="center"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Academic Excellence</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Comprehensive curriculum designed to foster critical thinking, creativity, and a love for learning.</p>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Expert Faculty</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Dedicated, qualified teachers committed to nurturing every student's potential.</p>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Holistic Development</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Focus on academics, sports, arts, and character building for well-rounded individuals.</p>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center hover:shadow-md transition-shadow">
                    <div class="h-12 w-12 rounded-lg bg-warning-100 dark:bg-warning-900/30 text-warning-600 dark:text-warning-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10m-2 2h-4m4 0h4"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Safe Environment</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">A secure, supportive campus where students feel valued and inspired to learn.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Academic Programs"
                subtitle="Our curriculum is designed to challenge, inspire, and prepare students for future success."
                align="center"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">JSS 1</h3>
                        </div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed mb-4">Foundation year building strong fundamentals in Mathematics, English, Science, and introductory subjects.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">Mathematics</span>
                            <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">English</span>
                            <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">Science</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">JSS 2</h3>
                        </div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed mb-4">Intermediate year deepening subject knowledge and introducing more complex concepts across all disciplines.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-accent-100 dark:bg-accent-900/30 px-2.5 py-0.5 text-xs font-medium text-accent-700 dark:text-accent-300">Mathematics</span>
                            <span class="inline-flex items-center rounded-full bg-accent-100 dark:bg-accent-900/30 px-2.5 py-0.5 text-xs font-medium text-accent-700 dark:text-accent-300">English</span>
                            <span class="inline-flex items-center rounded-full bg-accent-100 dark:bg-accent-900/30 px-2.5 py-0.5 text-xs font-medium text-accent-700 dark:text-accent-300">Science</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">JSS 3</h3>
                        </div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed mb-4">Final JSS year focused on examination preparation, advanced topics, and transition to senior secondary.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Exam Prep</span>
                            <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Advanced</span>
                            <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:text-success-300">Careers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-dark-surface py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="A Message from Our Principal"
                subtitle="Leading with vision, compassion, and an unwavering commitment to excellence."
                align="center"
            />

            <div class="max-w-4xl mx-auto bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3">
                    <div class="md:col-span-1 bg-gradient-to-br from-primary-100 to-accent-100 dark:from-primary-900/30 dark:to-accent-900/30 flex items-center justify-center p-8">
                        <div class="h-24 w-24 rounded-full bg-white dark:bg-dark-surface flex items-center justify-center text-2xl font-bold text-primary-700 dark:text-primary-300 shadow-sm">
                            PC
                        </div>
                    </div>
                    <div class="md:col-span-2 p-6 lg:p-8">
                        <svg class="h-8 w-8 text-primary-300 dark:text-primary-700 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4.995v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4.995v10h-9.983z"/></svg>
                        <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed mb-4">
                            At Greenfield Academy, we are committed to providing an environment where every student can thrive. Our dedicated team of educators works tirelessly to ensure that each child receives the attention and support they need to reach their full potential.
                        </p>
                        <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed mb-6">
                            We invite you to join our community and experience the Greenfield difference firsthand.
                        </p>
                        <div>
                            <p class="font-semibold text-neutral-900 dark:text-white">Principal Catherine</p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Principal, Greenfield Academy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Latest News"
                subtitle="Stay updated with the latest happenings at Greenfield Academy."
                align="center"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-public.news-card
                    title="Welcome Back to School"
                    excerpt="We are excited to welcome all our students back for the 2026/2027 academic session. School resumes on September 1st."
                    date="August 28, 2026"
                    author="Admin"
                    href="#"
                />
                <x-public.news-card
                    title="PTA Meeting This Saturday"
                    excerpt="All parents are cordially invited to attend our quarterly Parent-Teacher Association meeting this Saturday at 10am."
                    date="August 25, 2026"
                    author="Admin"
                    href="#"
                />
                <x-public.news-card
                    title="Science Fair Winners Announced"
                    excerpt="Congratulations to all participants in this year's Science Fair. The winning projects showcased incredible innovation."
                    date="August 20, 2026"
                    author="Mrs. Smith"
                    href="#"
                />
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-dark-surface py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Upcoming Events"
                subtitle="Mark your calendar with our upcoming school events and activities."
                align="center"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-public.event-card
                    title="New Student Orientation"
                    date="2026-09-01"
                    time="09:00 AM"
                    location="Main Hall"
                    description="Welcome session for all new students joining Greenfield Academy this term."
                    href="#"
                />
                <x-public.event-card
                    title="Inter-House Sports Competition"
                    date="2026-09-15"
                    time="08:00 AM"
                    location="School Field"
                    description="Annual inter-house sports competition featuring track and field events."
                    href="#"
                />
                <x-public.event-card
                    title="Parent-Teacher Conference"
                    date="2026-10-05"
                    time="10:00 AM"
                    location="Classrooms"
                    description="Discuss student progress and academic performance with class teachers."
                    href="#"
                />
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Student Life"
                subtitle="A vibrant community where learning extends beyond the classroom."
                align="center"
            />

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-public.gallery-item title="Classroom Learning" category="Academics" />
                <x-public.gallery-item title="Sports Day" category="Athletics" />
                <x-public.gallery-item title="Science Lab" category="STEM" />
                <x-public.gallery-item title="Art Class" category="Creative Arts" />
                <x-public.gallery-item title="Library" category="Resources" />
                <x-public.gallery-item title="Music Room" category="Performing Arts" />
                <x-public.gallery-item title="Assembly" category="Community" />
                <x-public.gallery-item title="Field Trip" category="Experiential" />
            </div>
        </div>
    </section>

    <section class="bg-white dark:bg-dark-surface py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="What Parents Say"
                subtitle="Hear from our school community about the Greenfield experience."
                align="center"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-public.testimonial
                    quote="Greenfield Academy has been a blessing for our family. The teachers genuinely care about each student's progress and well-being."
                    author="Mr. and Mrs. Doe"
                    role="Parent, JSS 1"
                    avatar="D"
                />
                <x-public.testimonial
                    quote="The curriculum is challenging yet engaging. My daughter has grown so much academically and socially since joining."
                    author="Mr. and Mrs. Smith"
                    role="Parent, JSS 2"
                    avatar="S"
                />
                <x-public.testimonial
                    quote="I love my teachers and friends here. The school feels like a second home, and I look forward to coming every day."
                    author="Jane Smith"
                    role="Student, JSS 2"
                    avatar="J"
                />
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Our Facilities"
                subtitle="State-of-the-art resources to support every aspect of learning and development."
                align="center"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-public.facility-card title="Modern Classrooms" description="Spacious, well-lit classrooms equipped with interactive whiteboards and comfortable seating." icon="book-open" />
                <x-public.facility-card title="Science Laboratory" description="Fully equipped lab for hands-on experiments in physics, chemistry, and biology." icon="flask-conical" />
                <x-public.facility-card title="Computer Lab" description="Modern computer lab with high-speed internet and latest software for digital learning." icon="cpu" />
                <x-public.facility-card title="Library & Resource Center" description="Extensive collection of books, journals, and digital resources for research and reading." icon="book-open" />
            </div>
        </div>
    </section>

    <section class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.cta-banner
                title="Ready to Join Greenfield Academy?"
                subtitle="Applications for the 2026/2027 academic session are now open. Take the first step toward an exceptional education."
                :primaryCta="['href' => '/admissions', 'label' => 'Apply Now']"
                :secondaryCta="['href' => '/contact', 'label' => 'Schedule a Visit']"
                background="primary"
            />
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-header
                title="Get in Touch"
                subtitle="We'd love to hear from you. Contact us for admissions, inquiries, or to schedule a campus tour."
                align="center"
            />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center">
                    <div class="h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">Email Us</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">info@greenfieldacademy.edu</p>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center">
                    <div class="h-10 w-10 rounded-lg bg-accent-100 dark:bg-accent-900/30 text-accent-600 dark:text-accent-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">Call Us</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">+234 800 000 0000</p>
                </div>
                <div class="bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm text-center">
                    <div class="h-10 w-10 rounded-lg bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1">Visit Us</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">123 Education Lane, Greenfield City</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-neutral-900 dark:bg-black py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-center sm:text-left">
                    <h3 class="text-lg font-semibold text-white mb-1">Stay Informed</h3>
                    <p class="text-sm text-neutral-400">Subscribe to our newsletter for school updates and announcements.</p>
                </div>
                <form class="flex gap-2 w-full sm:w-auto" onsubmit="event.preventDefault();">
                    <label for="home-newsletter" class="sr-only">Email address</label>
                    <input id="home-newsletter" type="email" placeholder="Your email address" class="flex-1 sm:w-64 rounded-lg border border-neutral-700 bg-neutral-800 text-white placeholder-neutral-400 px-4 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button type="submit" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 bg-primary-600 hover:bg-primary-700 text-white text-sm px-4 py-2 shadow-sm">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.guest>
