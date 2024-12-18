document.getElementById('testForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const answers = formData.getAll('answers[]');
    const totalQuestions = parseInt(formData.get('total_questions'), 10);
    const wordsData = JSON.parse(formData.get('words_data'));

    let correctCount = 0;
    let allCorrect = true;
    wordsData.forEach((word, index) => {
        const correctWord = word.word;
        const selectedAnswer = answers[index] || '';

        const questionDiv = form.querySelector(`.question:nth-child(${index + 1})`);
        const labels = questionDiv.querySelectorAll('label');
        let isAnswered = false;

        labels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');

            if (radio.checked) {
                isAnswered = true;
            }

            if (radio.value === correctWord) {
                label.classList.add('correct');
                if (radio.checked) {
                    correctCount++;
                }
            } else if (radio.checked && radio.value !== correctWord) {
                label.classList.add('incorrect');
                allCorrect = false;
            }
        });

        if (!isAnswered) {
            labels.forEach(label => {
                const radio = label.querySelector('input[type="radio"]');
                if (radio.value === correctWord) {
                    label.classList.add('unselected');
                }
            });
        }
    });


    Swal.fire({
        title: `Test Results ${Math.round(correctCount / totalQuestions * 100)}%`,
        text: `You got ${correctCount} out of ${totalQuestions} correct!`,
        icon: "success",
        confirmButtonText: 'OK'
    });

    form.querySelectorAll('input[type="radio"]').forEach(input => {
        input.disabled = true;
    });
});

document.querySelector('form').addEventListener('change', (event) => {
    const target = event.target.name;
    if (target === 'num_words' || target === 'filter') {
        event.target.form.submit();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('refresh-test').addEventListener('click', () => {
        window.location.reload();
    });
});