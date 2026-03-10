<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Research Details')] class extends Component {
    public int $index;

    public function groups(): array
    {
        return [
            [
                'title' => 'Smart Irrigation System Using IoT and ML',
                'leader' => 'Maria Santos',
                'members' => ['Juan dela Cruz', 'Ana Reyes', 'Carlo Mendoza'],
                'program' => 'BSCS',
                'section' => '4-A',
                'year' => '2024',
                'advisor' => 'Dr. Ramon Torres',
                'abstract' => 'An automated irrigation system that leverages IoT sensors and machine learning algorithms to optimize water usage in agricultural fields based on real-time soil moisture, weather data, and crop requirements.',
                'keywords' => ['IoT', 'Machine Learning', 'Agriculture', 'Automation'],
                'introduction' => 'Agriculture remains a critical sector globally, yet inefficient water use continues to challenge farmers especially in developing regions. Traditional irrigation methods rely on fixed schedules regardless of actual soil conditions, leading to overwatering, crop damage, and excessive water consumption. This study aims to develop a smart irrigation system that integrates IoT sensor networks with machine learning-based prediction models to dynamically regulate water delivery based on real-time environmental data.',
                'methodology' => 'The system uses a network of soil moisture sensors, temperature/humidity probes, and weather API feeds connected to a Raspberry Pi central hub. Data is transmitted to a cloud server where a trained LSTM model predicts optimal irrigation timing and duration. A mobile dashboard allows farmers to monitor and override the system. The ML model was trained on 6 months of agricultural sensor data collected from regional farm test sites.',
                'results' => 'Field trials over 8 weeks demonstrated a 38% reduction in water usage compared to traditional schedule-based irrigation, while maintaining equivalent or improved crop yield metrics. The LSTM prediction model achieved 91.4% accuracy in forecasting irrigation need 24 hours in advance. System uptime was maintained at 99.1% across all test deployments.',
                'conclusion' => 'The proposed smart irrigation system effectively reduces water waste while maintaining agricultural productivity. Integration of IoT and ML technologies provides a scalable, cost-effective solution suitable for deployment in resource-constrained farming environments. Future work will extend the model to support multi-crop differentiation and integrate satellite imagery for field-wide coverage.',
                'panel' => ['Dr. Angelica Reyes', 'Prof. Marco Santos', 'Dr. Luis Villanueva'],
                'date_defended' => 'November 14, 2024',
                'grade' => '1.25',
                'file' => null,
            ],
            [
                'title' => 'AI-Powered Early Detection of Diabetic Retinopathy',
                'leader' => 'Jose Bautista',
                'members' => ['Liza Fernandez', 'Miguel Ramos', 'Trisha Villanueva'],
                'program' => 'BSIT',
                'section' => '4-B',
                'year' => '2024',
                'advisor' => 'Prof. Elena Castillo',
                'abstract' => 'A deep learning-based diagnostic tool that analyzes retinal fundus images to detect early signs of diabetic retinopathy, enabling timely medical intervention and reducing the risk of blindness in diabetic patients.',
                'keywords' => ['Deep Learning', 'Medical Imaging', 'Healthcare', 'CNN'],
                'introduction' => 'Diabetic retinopathy (DR) is a leading cause of preventable blindness worldwide, affecting approximately one-third of people with diabetes. Early detection is crucial for effective treatment, yet access to ophthalmologists is limited in many areas. This study presents an AI-powered screening system that leverages convolutional neural networks to classify DR severity from retinal fundus photographs, enabling early and affordable screening at primary care level.',
                'methodology' => 'A CNN architecture based on EfficientNet-B4 was fine-tuned on the publicly available APTOS 2019 Blindness Detection dataset (3,662 images) augmented with 1,200 locally collected retinal images. The model classifies DR into five severity stages (No DR, Mild, Moderate, Severe, Proliferative). Preprocessing included CLAHE enhancement, green-channel extraction, and circular crop normalization. Training used a cosine annealing learning rate schedule with focal loss to handle class imbalance.',
                'results' => 'The model achieved a quadratic weighted kappa score of 0.932 on the validation set, surpassing the baseline clinical performance benchmark of 0.85. Sensitivity for detecting referable DR (moderate or above) was 94.7% with specificity of 91.2%. Inference time averaged 1.3 seconds per image on standard CPU hardware, making it viable for clinic deployment.',
                'conclusion' => 'The AI-based DR screening tool demonstrates clinically actionable accuracy and can serve as a first-line diagnostic aid in under-resourced healthcare settings. Deployment as a web-based application allows integration with existing EMR systems. Continued validation across diverse demographic groups and imaging devices is recommended before widespread clinical adoption.',
                'panel' => ['Dr. Cynthia Macaraeg', 'Prof. Antonio Reyes', 'Dr. Helen Sy'],
                'date_defended' => 'October 22, 2024',
                'grade' => '1.00',
                'file' => null,
            ],
            [
                'title' => 'Blockchain-Based Academic Credential Verification',
                'leader' => 'Rachel Gomez',
                'members' => ['Paolo Aquino', 'Nina Coronel'],
                'program' => 'BSCS',
                'section' => '4-C',
                'year' => '2023',
                'advisor' => 'Dr. Ramon Torres',
                'abstract' => 'A decentralized platform using blockchain technology to issue, store, and verify academic credentials, eliminating document fraud and enabling instant verification by employers and institutions worldwide.',
                'keywords' => ['Blockchain', 'Credential Verification', 'Decentralization', 'Security'],
                'introduction' => 'Academic credential fraud is a growing global problem. Forged transcripts, diplomas, and certificates undermine institutional trust and disadvantage legitimate graduates. Centralized verification systems are slow, expensive, and dependent on institutional availability. This study proposes a blockchain-based credential management system that enables tamper-proof issuance and third-party verification without requiring direct contact with the issuing institution.',
                'methodology' => 'Smart contracts on the Ethereum blockchain (Sepolia testnet) govern credential issuance and revocation. Credentials are stored as JSON-LD structures hashed using SHA-256 and anchored on-chain. A QR-code-based verification interface allows employers to authenticate credentials instantly. The system was evaluated against ISO/IEC 27001 security standards. A React.js frontend interfaces with MetaMask for institutional signing.',
                'results' => 'Verification time was reduced from an average of 5.2 business days (traditional process) to under 3 seconds. Zero false positives were recorded across 200 simulated verification attempts. Gas costs averaged $0.012 per credential issuance on the Sepolia testnet. User acceptance testing with 45 HR professionals yielded a 4.6/5 usability score.',
                'conclusion' => 'Blockchain technology provides a viable foundation for a fraud-resistant academic credentialing ecosystem. The proposed system is interoperable, auditable, and eliminates reliance on centralized record-keeping. Migration to a Layer-2 solution is recommended to reduce gas costs for production deployment at scale.',
                'panel' => ['Prof. Ronald Diaz', 'Dr. Maricel Tan', 'Prof. Marco Santos'],
                'date_defended' => 'May 18, 2023',
                'grade' => '1.50',
                'file' => null,
            ],
            [
                'title' => 'Sentiment Analysis of Student Feedback Using NLP',
                'leader' => 'Kevin Lim',
                'members' => ['Sofia Tan', 'Dennis Abad', 'Pearl Navarro', 'Roy Sy'],
                'program' => 'BSIT',
                'section' => '3-A',
                'year' => '2023',
                'advisor' => 'Prof. Elena Castillo',
                'abstract' => 'A natural language processing system that automatically analyzes and categorizes student feedback from course evaluations, providing instructors and administrators with actionable insights to improve teaching quality.',
                'keywords' => ['NLP', 'Sentiment Analysis', 'Education', 'Text Mining'],
                'introduction' => 'Course evaluation feedback collected from students is often voluminous and unstructured, making manual analysis impractical at scale. Relevant insights about teaching quality, curriculum gaps, and student satisfaction are frequently lost. This study develops an automated sentiment analysis pipeline that processes free-text evaluation responses and surfaces structured insights to faculty administrators through a dashboard interface.',
                'methodology' => 'A BERT-based fine-tuned model (bert-base-uncased) was trained on 12,000 labeled student evaluation comments sourced from three universities. Labels covered sentiment polarity (positive/neutral/negative) and aspect categories (teaching quality, difficulty, environment, assessments). Text preprocessing included stopword removal, lemmatization, and emoji normalization. The dashboard was built with Laravel and Chart.js, integrating a Python Flask API endpoint for inference.',
                'results' => 'The model achieved 89.3% accuracy and F1-score of 0.887 on the held-out test set. Processing a typical 500-response evaluation batch takes under 40 seconds. In a pilot deployment at two departments, administrators reported that the dashboard reduced feedback review time by 72% and surfaced 3.4x more actionable insights compared to manual sampling.',
                'conclusion' => 'The NLP-powered evaluation analysis system significantly reduces the administrative burden of processing student feedback while improving insight quality. The aspect-based categorization enables targeted faculty development interventions. Future enhancements will include multilingual support for Filipino-language responses and integration with existing LMS platforms.',
                'panel' => ['Dr. Angelica Reyes', 'Prof. Ronald Diaz', 'Dr. Cynthia Macaraeg'],
                'date_defended' => 'June 3, 2023',
                'grade' => '1.25',
                'file' => null,
            ],
            [
                'title' => 'Real-Time Sign Language Recognition via Computer Vision',
                'leader' => 'Grace Lacson',
                'members' => ['Nico Valdez', 'Camille Ocampo'],
                'program' => 'BSCS',
                'section' => '4-D',
                'year' => '2024',
                'advisor' => 'Dr. Ramon Torres',
                'abstract' => 'A computer vision application that performs real-time recognition of Filipino Sign Language (FSL) hand gestures using convolutional neural networks, bridging communication barriers for the deaf and hard-of-hearing community.',
                'keywords' => ['Computer Vision', 'Sign Language', 'Accessibility', 'CNN'],
                'introduction' => 'The deaf and hard-of-hearing (DHH) community in the Philippines faces significant communication barriers due to limited sign language literacy among the hearing population and a shortage of qualified interpreters. This study develops a real-time FSL gesture recognition system using computer vision and deep learning to translate hand gestures into text and synthesized speech, reducing dependence on human interpreters in everyday interactions.',
                'methodology' => 'A custom dataset of 15,000 FSL gesture images was collected across 42 sign classes using a standardized capture protocol with 28 participants of varying hand sizes and skin tones. MediaPipe was used for hand landmark extraction, feeding a CNN-LSTM hybrid model trained on normalized landmark sequences. The application was deployed as a cross-platform mobile app using Flutter with a TensorFlow Lite inference engine.',
                'results' => 'The model achieved 96.1% classification accuracy on the test set across 42 FSL signs. Real-time inference latency averaged 47ms on a mid-range Android device (Snapdragon 720G). User testing with 15 DHH participants yielded positive usability outcomes with a SUS score of 82.4 (Grade B, "Good"). Recognition performance degraded to 88.7% under poor lighting conditions.',
                'conclusion' => 'The proposed system demonstrates that cost-accessible mobile hardware is sufficient for practical real-time FSL recognition. The landmark-based approach generalizes well across diverse users with minimal pre-processing overhead. Expanding the gesture vocabulary to full FSL sentences and incorporating context-aware language models are identified as key directions for future development.',
                'panel' => ['Prof. Antonio Reyes', 'Dr. Helen Sy', 'Dr. Luis Villanueva'],
                'date_defended' => 'November 28, 2024',
                'grade' => '1.00',
                'file' => null,
            ],
            [
                'title' => 'Predictive Analytics for Student Academic Performance',
                'leader' => 'Andrei Cruz',
                'members' => ['Bianca Flores', 'Lance Ong', 'Mia Salazar'],
                'program' => 'BSIT',
                'section' => '3-B',
                'year' => '2023',
                'advisor' => 'Prof. Elena Castillo',
                'abstract' => 'A predictive modeling system that identifies at-risk students early in the semester by analyzing academic history, attendance, and behavioral patterns, enabling targeted intervention before grades deteriorate.',
                'keywords' => ['Predictive Analytics', 'Education', 'Data Mining', 'Early Intervention'],
                'introduction' => 'Student academic failure often has early warning signals that go unnoticed until grades have already declined significantly. Proactive identification of at-risk students enables timely guidance counseling and academic support. This study builds a predictive analytics system using historical student data to flag students at risk of failing by the 6th week of the semester, giving faculty and advisors a six-week window for intervention.',
                'methodology' => 'Data from 3,200 student records spanning 4 academic years was used, with features including GPA trend, attendance rate, LMS login frequency, assignment submission timeliness, and demographic factors. Four models were evaluated: logistic regression, random forest, XGBoost, and a feedforward neural network. SMOTE oversampling addressed class imbalance (14% at-risk rate). Features were selected using recursive feature elimination (RFE). The system dashboard was built in Laravel with role-based access for advisors.',
                'results' => 'XGBoost achieved the best performance with AUC-ROC of 0.914 and F1-score of 0.871 on the held-out test set. Attendance rate and GPA trend were the top two predictive features. In a prospective pilot with 180 students, 83% of students flagged as high-risk were confirmed to have required academic intervention by midterms. Advisor dashboard adoption reached 91% after a two-week onboarding period.',
                'conclusion' => 'Early-semester predictive modeling is a viable and impactful tool for academic intervention programs. The XGBoost model provides both accuracy and interpretability suitable for advisor use. Institutional adoption requires careful attention to data privacy governance and model transparency. Future iterations will incorporate real-time LMS API feeds and extend prediction to course-level failure risk.',
                'panel' => ['Dr. Maricel Tan', 'Prof. Marco Santos', 'Dr. Angelica Reyes'],
                'date_defended' => 'May 30, 2023',
                'grade' => '1.25',
                'file' => null,
            ],
        ];
    }

    public function group(): array
    {
        $groups = $this->groups();

        abort_if(!isset($groups[$this->index]), 404);

        return $groups[$this->index];
    }

    public function mount(int $index): void
    {
        $this->index = $index;
        abort_if(!isset($this->groups()[$this->index]), 404);
    }
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

<div class="relative min-h-screen" style="background: #F8FAFC">

    {{-- Ambient background glows --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 h-[400px] w-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    @php $group = $this->group(); @endphp

    <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Back + Breadcrumb ───────────────────────── --}}
        <div class="mb-7 flex items-center gap-2" style="font-size: 12px; color: #94A3B8">
            <a href="{{ route('repository') }}" wire:navigate
                class="inline-flex items-center gap-1.5 transition-colors hover:underline" style="color: #64748B">
                Research Repository
            </a>
            <span style="color: #CBD5E1">/</span>
            <span class="truncate"
                style="color: #0052FF; font-weight: 500; max-width: 280px">{{ $group['title'] }}</span>
        </div>

        {{-- ── Hero Card ────────────────────────────────── --}}
        <div class="mb-6 overflow-hidden rounded-2xl border"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.06)">

            {{-- Gradient top stripe --}}
            <div class="h-1 w-full" style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

            <div class="p-6 sm:p-8">

                {{-- Badges row --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em">
                        {{ $group['program'] }}
                    </span>
                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                        style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                        Section {{ $group['section'] }}
                    </span>
                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                        style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                        A.Y. {{ $group['year'] }}
                    </span>
                </div>

                {{-- Title --}}
                <h1 class="mb-4 leading-snug"
                    style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.4rem, 3vw, 2rem); letter-spacing: -0.01em; color: #0F172A">
                    {{ $group['title'] }}
                </h1>

                {{-- Keywords --}}
                <div class="mb-6 flex flex-wrap gap-1.5">
                    @foreach ($group['keywords'] as $keyword)
                        <span class="rounded-lg border px-2.5 py-0.5 text-xs"
                            style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                            {{ $keyword }}
                        </span>
                    @endforeach
                </div>

                {{-- Meta grid --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest"
                            style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Adviser
                        </p>
                        <p class="text-sm font-medium" style="color: #0F172A">{{ $group['advisor'] }}</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest"
                            style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Members
                        </p>
                        <p class="text-sm font-medium" style="color: #0F172A">{{ count($group['members']) + 1 }}
                            students</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Two-column layout ────────────────────────── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left: abstract + download --}}
            <div class="space-y-5 lg:col-span-2">

                {{-- Abstract --}}
                <div class="rounded-2xl border p-6"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="mb-4 flex items-center gap-3">
                        <h2 class="font-semibold" style="color: #0F172A">Abstract</h2>
                    </div>
                    <p class="text-sm leading-relaxed" style="color: #475569">{{ $group['abstract'] }}</p>
                </div>

                {{-- Download --}}
                <div class="rounded-2xl border p-6"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <h2 class="mb-1 font-semibold" style="color: #0F172A">Research Paper</h2>
                    <p class="mb-5 text-sm" style="color: #94A3B8">Download the full research paper in PDF format.</p>

                    @if ($group['file'])
                        <a href="{{ asset('storage/' . $group['file']) }}" download
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90"
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF)">
                            Download PDF
                        </a>
                    @else
                        <div class="rounded-xl border border-dashed px-5 py-4 text-center"
                            style="border-color: #CBD5E1; background: #F8FAFC">
                            <p class="text-sm font-medium" style="color: #94A3B8">No file uploaded yet.</p>
                            <p class="mt-0.5 text-xs" style="color: #CBD5E1">The full paper will be available once
                                uploaded by the research team.</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Right: sidebar --}}
            <div class="space-y-5">

                {{-- Group Members --}}
                <div class="rounded-2xl border p-5"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <h3 class="mb-4 text-sm font-semibold" style="color: #0F172A">Research Team</h3>

                    {{-- Leader --}}
                    <div class="mb-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-widest"
                            style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Leader
                        </p>
                        <div class="flex items-center gap-2.5">
                            <div>
                                <p class="text-sm font-semibold" style="color: #0F172A">{{ $group['leader'] }}</p>
                                <p class="text-xs" style="color: #94A3B8">Group Leader</p>
                            </div>
                        </div>
                    </div>

                    <div class="my-3 h-px" style="background: #F1F5F9"></div>

                    {{-- Members --}}
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-widest"
                            style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px">Members
                        </p>
                        <div class="space-y-2.5">
                            @foreach ($group['members'] as $member)
                                <div class="flex items-center gap-2.5">
                                    <p class="text-sm" style="color: #475569">{{ $member }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Adviser --}}
                <div class="rounded-2xl border p-5"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <h3 class="mb-3 text-sm font-semibold" style="color: #0F172A">Adviser</h3>
                    <div class="flex items-center gap-3">
                        <div>
                            <p class="text-sm font-semibold" style="color: #0F172A">{{ $group['advisor'] }}</p>
                            <p class="text-xs" style="color: #94A3B8">Research Adviser</p>
                        </div>
                    </div>
                </div>

                {{-- Panel Members --}}
                <div class="rounded-2xl border p-5"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <h3 class="mb-3 text-sm font-semibold" style="color: #0F172A">Panel Members</h3>
                    <div class="space-y-2.5">
                        @foreach ($group['panel'] as $panelist)
                            <div class="flex items-center gap-2.5">
                                <p class="text-sm" style="color: #475569">{{ $panelist }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
