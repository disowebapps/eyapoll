<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete - Echara Youths</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Success Message -->
            <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Registration Complete!</h1>
                <p class="text-gray-600 mb-6 text-sm sm:text-base">
                    Welcome to Echara Youths! Your voter account has been successfully created.
                </p>


                <!-- Next Steps -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2">What's next?</h3>
                    <ul class="text-sm text-blue-800 space-y-1 text-left">
                        <li>• Sign in and complete your identity verification</li>
                        <li>• Apply as candidate or vote during elections</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <a href="{{ route('voter.login') }}"
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition inline-block text-center">
                        Sign In to Your Account
                    </a>
                    <a href="{{ route('home') }}"
                       class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition inline-block text-center">
                        Return to Home
                    </a>
                </div>
            </div>

            <!-- Support -->
            <div class="mt-6">
                <p class="text-sm text-gray-600">
                    Questions?
                    <button class="text-blue-600 hover:text-blue-500 font-medium"
                            onclick="alert('Contact support at support@echara.org')">
                        Contact Support
                    </button>
                </p>
            </div>
        </div>
    </div>
</body>
</html>