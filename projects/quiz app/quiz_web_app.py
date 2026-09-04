import streamlit as st
import random

python_questions = [
    {"question": "What does print('Hello') do?",
     "choices": ["Stores 'Hello' in memory", "Displays 'Hello' on the screen", "Deletes 'Hello'", "Creates a file named 'Hello'"],
     "answer": "Displays 'Hello' on the screen",
     "explanation": "print() outputs text to the console."},
    {"question": "Which of these is a valid Python variable name?",
     "choices": ["2score", "my-score", "my_score", "my score"],
     "answer": "my_score",
     "explanation": "Names can't start with a number, use hyphens, or contain spaces."},
    {"question": "What type is the value 42?",
     "choices": ["str", "float", "bool", "int"],
     "answer": "int",
     "explanation": "Whole numbers without a decimal point are integers."},
    {"question": "How do you start a comment in Python?",
     "choices": ["//", "#", "/*", "--"],
     "answer": "#",
     "explanation": "Python uses # for single-line comments."},
    {"question": "What does a for loop do?",
     "choices": ["Runs code only once", "Repeats code for each item in a sequence", "Asks the user a question", "Defines a function"],
     "answer": "Repeats code for each item in a sequence",
     "explanation": "A for loop iterates over a sequence and runs its body for each item."},
    {"question": "Which keyword defines a function in Python?",
     "choices": ["function", "define", "def", "fn"],
     "answer": "def",
     "explanation": "Python uses 'def' to define a function."},
    {"question": "What does len('hello') return?",
     "choices": ["4", "5", "6", "Error"],
     "answer": "5",
     "explanation": "len() counts the characters in a string."},
    {"question": "Which data type is immutable in Python?",
     "choices": ["list", "dict", "tuple", "set"],
     "answer": "tuple",
     "explanation": "Tuples cannot be changed once created; lists, dicts, and sets can."},
    {"question": "What does the 'append' method do to a list?",
     "choices": ["Removes the last item", "Adds an item to the end", "Sorts the list", "Reverses the list"],
     "answer": "Adds an item to the end",
     "explanation": "append() adds a single element to the end of a list."},
    {"question": "What is the output of 3 ** 2 in Python?",
     "choices": ["6", "9", "5", "Error"],
     "answer": "9",
     "explanation": "The ** operator raises a number to a power. 3 squared is 9."},
    {"question": "Which keyword is used to handle exceptions?",
     "choices": ["catch", "except", "rescue", "handle"],
     "answer": "except",
     "explanation": "Python uses try/except blocks for error handling."},
    {"question": "What does 'import' do in Python?",
     "choices": ["Deletes a module", "Brings in code from another file or library", "Creates a new file", "Runs the program"],
     "answer": "Brings in code from another file or library",
     "explanation": "import lets you use functions and classes defined elsewhere."},
    {"question": "What is the correct file extension for Python files?",
     "choices": [".py", ".pt", ".pyt", ".python"],
     "answer": ".py",
     "explanation": "Python source files use the .py extension."},
    {"question": "How do you create a dictionary in Python?",
     "choices": ["[]", "()", "{}", "<>"],
     "answer": "{}",
     "explanation": "Curly braces (with key: value pairs) create a dictionary."},
    {"question": "What does 'None' represent in Python?",
     "choices": ["Zero", "An empty string", "The absence of a value", "False"],
     "answer": "The absence of a value",
     "explanation": "None is Python's way of representing 'nothing' or 'no value'."},
]

java_questions = [
    {"question": "What does 'public static void main' represent in Java?",
     "choices": ["A variable", "The entry point of a program", "A comment", "A loop"],
     "answer": "The entry point of a program",
     "explanation": "Every Java application starts running from the main method."},
    {"question": "Which keyword is used to create a class in Java?",
     "choices": ["class", "struct", "object", "define"],
     "answer": "class",
     "explanation": "Java uses the 'class' keyword to define a class."},
    {"question": "How do you print text to the console in Java?",
     "choices": ["print('text')", "System.out.println('text')", "echo 'text'", "console.log('text')"],
     "answer": "System.out.println('text')",
     "explanation": "System.out.println() writes a line of text to standard output."},
    {"question": "Which of these is a valid Java variable declaration?",
     "choices": ["int 1number;", "int number1;", "int number-1;", "int number 1;"],
     "answer": "int number1;",
     "explanation": "Java variable names can't start with a digit or contain spaces/hyphens."},
    {"question": "What symbol ends most statements in Java?",
     "choices": [".", ",", ";", ":"],
     "answer": ";",
     "explanation": "Java statements are terminated with a semicolon."},
    {"question": "Which of these is NOT a primitive type in Java?",
     "choices": ["int", "boolean", "String", "double"],
     "answer": "String",
     "explanation": "String is a class (object type), not a primitive."},
    {"question": "What does the 'extends' keyword do?",
     "choices": ["Creates a variable", "Inherits from a class", "Imports a package", "Defines an interface"],
     "answer": "Inherits from a class",
     "explanation": "'extends' lets one class inherit fields and methods from another."},
    {"question": "Which loop guarantees at least one execution?",
     "choices": ["for", "while", "do-while", "foreach"],
     "answer": "do-while",
     "explanation": "A do-while loop checks its condition after running the body once."},
    {"question": "What is the size of an int in Java?",
     "choices": ["16 bits", "32 bits", "64 bits", "8 bits"],
     "answer": "32 bits",
     "explanation": "Java's int type is always a 32-bit signed integer."},
    {"question": "Which keyword prevents a class from being subclassed?",
     "choices": ["static", "private", "final", "const"],
     "answer": "final",
     "explanation": "A 'final' class cannot be extended by another class."},
    {"question": "What does JVM stand for?",
     "choices": ["Java Virtual Machine", "Java Variable Method", "Java Verified Module", "Java Visual Model"],
     "answer": "Java Virtual Machine",
     "explanation": "The JVM runs compiled Java bytecode on any platform."},
    {"question": "Which collection type does not allow duplicate elements?",
     "choices": ["List", "Set", "Map", "Array"],
     "answer": "Set",
     "explanation": "A Set enforces uniqueness among its elements."},
    {"question": "How do you create an object of class Car in Java?",
     "choices": ["Car myCar = new Car();", "new Car myCar();", "Car myCar();", "create Car myCar;"],
     "answer": "Car myCar = new Car();",
     "explanation": "The 'new' keyword allocates a new object of the given class."},
    {"question": "Which access modifier makes a member visible everywhere?",
     "choices": ["private", "protected", "public", "default"],
     "answer": "public",
     "explanation": "'public' members can be accessed from any other class."},
    {"question": "What is method overloading?",
     "choices": ["Same method name, different parameters", "Same method name, same parameters", "Overriding a parent method", "Deleting a method"],
     "answer": "Same method name, different parameters",
     "explanation": "Overloading lets a class have multiple methods with the same name but different signatures."},
]

csharp_questions = [
    {"question": "What does 'Console.WriteLine' do in C#?",
     "choices": ["Reads user input", "Prints text with a new line", "Declares a variable", "Ends the program"],
     "answer": "Prints text with a new line",
     "explanation": "Console.WriteLine outputs text followed by a line break."},
    {"question": "Which keyword declares a variable that cannot be reassigned?",
     "choices": ["var", "const", "static", "dynamic"],
     "answer": "const",
     "explanation": "'const' creates a compile-time constant that can't be changed."},
    {"question": "What symbol is used for single-line comments in C#?",
     "choices": ["#", "//", "<!--", "'"],
     "answer": "//",
     "explanation": "C# uses // for single-line comments, similar to Java and C++."},
    {"question": "Which of these is a value type in C#?",
     "choices": ["string", "int", "object", "class"],
     "answer": "int",
     "explanation": "int is a value type; string and object are reference types."},
    {"question": "What is the entry point method for a C# console app?",
     "choices": ["Start()", "Main()", "Run()", "Init()"],
     "answer": "Main()",
     "explanation": "C# programs begin execution in the Main method."},
    {"question": "Which keyword is used to create a class in C#?",
     "choices": ["class", "struct", "object", "type"],
     "answer": "class",
     "explanation": "C# uses the 'class' keyword just like Java."},
    {"question": "What does the '??' operator do in C#?",
     "choices": ["Compares two values", "Provides a default if the value is null", "Throws an exception", "Loops through a collection"],
     "answer": "Provides a default if the value is null",
     "explanation": "The null-coalescing operator returns the right-hand value if the left is null."},
    {"question": "Which loop is best when the number of iterations is known?",
     "choices": ["while", "do-while", "for", "if"],
     "answer": "for",
     "explanation": "A for loop is ideal when you know exactly how many times to iterate."},
    {"question": "What does 'public' mean when applied to a class member?",
     "choices": ["Only accessible in the same class", "Accessible from any other code", "Accessible only in derived classes", "Not accessible at all"],
     "answer": "Accessible from any other code",
     "explanation": "'public' members have no access restrictions."},
    {"question": "Which collection does not allow duplicate keys?",
     "choices": ["List<T>", "Dictionary<K,V>", "Array", "Queue<T>"],
     "answer": "Dictionary<K,V>",
     "explanation": "A Dictionary maps unique keys to values."},
    {"question": "What is the correct file extension for C# source files?",
     "choices": [".cs", ".c#", ".csh", ".csx"],
     "answer": ".cs",
     "explanation": "C# source files use the .cs extension."},
    {"question": "Which keyword is used for inheritance in C#?",
     "choices": ["extends", ":", "inherits", "implements"],
     "answer": ":",
     "explanation": "C# uses a colon after the class name to indicate inheritance, e.g. class Dog : Animal."},
    {"question": "What does 'var' do in C#?",
     "choices": ["Declares a dynamic (runtime-typed) variable", "Declares a variable with an inferred type", "Declares a constant", "Declares a global variable"],
     "answer": "Declares a variable with an inferred type",
     "explanation": "'var' lets the compiler infer the type at compile time from the assigned value."},
    {"question": "Which of these is used to handle exceptions in C#?",
     "choices": ["try/catch", "try/except", "on error", "rescue"],
     "answer": "try/catch",
     "explanation": "C# uses try/catch/finally blocks for exception handling."},
    {"question": "What is the default access modifier for a class member in C#?",
     "choices": ["public", "private", "protected", "internal"],
     "answer": "private",
     "explanation": "Class members are private by default if no modifier is specified."},
]

question_banks = {
    "Python": python_questions,
    "Java": java_questions,
    "C#": csharp_questions,
}

difficulty_counts = {
    "Easy": 5,
    "Normal": 10,
    "Hard": 15,
}

st.set_page_config(page_title="Programming Quiz", page_icon="🧠")
st.title("Programming Quiz")
st.caption("Created by Kent T. Ampoloquio")

if "stage" not in st.session_state:
    st.session_state.stage = "menu"
    st.session_state.language = None
    st.session_state.difficulty = None
    st.session_state.quiz = []
    st.session_state.current = 0
    st.session_state.score = 0
    st.session_state.answered = False
    st.session_state.selected = None


def start_quiz(language, difficulty):
    count = difficulty_counts[difficulty]
    pool = question_banks[language].copy()
    random.shuffle(pool)
    st.session_state.quiz = pool[:count]
    st.session_state.language = language
    st.session_state.difficulty = difficulty
    st.session_state.current = 0
    st.session_state.score = 0
    st.session_state.answered = False
    st.session_state.selected = None
    st.session_state.stage = "quiz"


def play_again():
    start_quiz(st.session_state.language, st.session_state.difficulty)


def back_to_menu():
    st.session_state.stage = "menu"


if st.session_state.stage == "menu":
    st.subheader("Choose a language")
    language = st.radio(
        "Language",
        options=list(question_banks.keys()),
        label_visibility="collapsed",
        horizontal=True,
    )

    st.subheader("Choose a difficulty")
    difficulty = st.radio(
        "Difficulty",
        options=list(difficulty_counts.keys()),
        label_visibility="collapsed",
        horizontal=True,
    )
    st.caption(f"{difficulty_counts[difficulty]} questions")

    if st.button("Start quiz", type="primary"):
        start_quiz(language, difficulty)
        st.rerun()

elif st.session_state.stage == "quiz":
    quiz = st.session_state.quiz
    total = len(quiz)
    q = quiz[st.session_state.current]

    st.caption(f"{st.session_state.language} · {st.session_state.difficulty}")
    st.progress(st.session_state.current / total)
    st.subheader(f"Question {st.session_state.current + 1} of {total}")
    st.write(q["question"])

    for choice in q["choices"]:
        disabled = st.session_state.answered
        if st.button(choice, disabled=disabled, key=f"{st.session_state.current}-{choice}"):
            st.session_state.answered = True
            st.session_state.selected = choice
            if choice == q["answer"]:
                st.session_state.score += 1
            st.rerun()

    if st.session_state.answered:
        if st.session_state.selected == q["answer"]:
            st.success(f"✓ Correct! {q['explanation']}")
        else:
            st.error(f"✗ Wrong. The answer was **{q['answer']}**. {q['explanation']}")

        if st.session_state.current + 1 < total:
            if st.button("Next question →"):
                st.session_state.current += 1
                st.session_state.answered = False
                st.session_state.selected = None
                st.rerun()
        else:
            if st.button("See results →"):
                st.session_state.stage = "results"
                st.rerun()

elif st.session_state.stage == "results":
    total = len(st.session_state.quiz)
    score = st.session_state.score
    percentage = round((score / total) * 100)

    st.caption(f"{st.session_state.language} · {st.session_state.difficulty}")
    st.header(f"Your score: {score}/{total} ({percentage}%)")

    if percentage == 100:
        st.success("Perfect score! You really know your stuff! 🎉")
    elif percentage >= 70:
        st.success("Solid work! You've got the fundamentals down.")
    elif percentage >= 40:
        st.info("Good start! A bit more practice and you'll nail it.")
    else:
        st.warning("Keep going — every expert started somewhere!")

    col1, col2 = st.columns(2)
    with col1:
        if st.button("Play again (same settings)", type="primary"):
            play_again()
            st.rerun()
    with col2:
        if st.button("Choose new language/difficulty"):
            back_to_menu()
            st.rerun()