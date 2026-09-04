import random

questions = [
    {
        "question": "What does print('Hello') do?",
        "choices": ["A) Stores 'Hello' in memory", "B) Displays 'Hello' on the screen", "C) Deletes 'Hello'", "D) Creates a file"],
        "answer": "B",
        "explanation": "print() outputs text to the console."
    },
    {
        "question": "Which of these is a valid Python variable name?",
        "choices": ["A) 2score", "B) my-score", "C) my_score", "D) my score"],
        "answer": "C",
        "explanation": "Variable names can't start with a number, use hyphens, or have spaces."
    },
    {
        "question": "What type is the value 42?",
        "choices": ["A) str", "B) float", "C) bool", "D) int"],
        "answer": "D",
        "explanation": "Whole numbers without a decimal point are integers (int) in Python."
    },
    {
        "question": "How do you start a comment in Python?",
        "choices": ["A) //", "B) /*", "C) #", "D) --"],
        "answer": "C",
        "explanation": "Python uses # for single-line comments."
    },
    {
        "question": "What does a for loop do?",
        "choices": ["A) Runs code only once", "B) Asks the user a question", "C) Defines a function", "D) Repeats code for each item in a sequence"],
        "answer": "D",
        "explanation": "A for loop iterates over a sequence and runs its body for each item."
    },
    {
        "question": "Which keyword defines a function in Python?",
        "choices": ["A) function", "B) define", "C) fn", "D) def"],
        "answer": "D",
        "explanation": "Python uses 'def' to define a function."
    },
    {
        "question": "What does len('hello') return?",
        "choices": ["A) 4", "B) 5", "C) 6", "D) Error"],
        "answer": "B",
        "explanation": "len() counts the characters in a string. 'hello' has 5."
    },
]


def print_separator():
    print("-" * 45)


def run_quiz():
    print_separator()
    print("       Welcome to the Python Quiz!")
    print_separator()

    quiz = questions.copy()
    random.shuffle(quiz)

    score = 0
    total = len(quiz)

    for i, q in enumerate(quiz):
        print(f"\nQuestion {i + 1} of {total}:")
        print(q["question"])
        print()
        for choice in q["choices"]:
            print(f"  {choice}")
        print()

        while True:
            user_answer = input("Your answer (A/B/C/D): ").strip().upper()
            if user_answer in ["A", "B", "C", "D"]:
                break
            print("  Please enter A, B, C, or D.")

        if user_answer == q["answer"]:
            print("  ✓ Correct!")
            score += 1
        else:
            print(f"  ✗ Wrong. The answer was {q['answer']}.")

        print(f"  {q['explanation']}")
        print_separator()

    percentage = round((score / total) * 100)
    print(f"\nYou scored {score}/{total} ({percentage}%)")

    if percentage == 100:
        print("Perfect score! You're a Python natural!")
    elif percentage >= 70:
        print("Solid work! You've got the fundamentals down.")
    elif percentage >= 40:
        print("Good start! A bit more practice and you'll nail it.")
    else:
        print("Keep going — every expert started somewhere!")

    print_separator()

    play_again = input("\nPlay again? (yes/no): ").strip().lower()
    if play_again in ["yes", "y"]:
        run_quiz()
    else:
        print("Thanks for playing!")

if __name__ == "__main__":
    run_quiz()