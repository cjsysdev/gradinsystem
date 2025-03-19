#include <stdio.h>

void displayMenu();
void playQuiz();
int checkAnswer(int userAnswer, int correctAnswer);

int main() {
    int choice;

    while (1) { 
        displayMenu();
        printf("Enter your choice: ");
        scanf("%d", &choice);

        switch (choice) {
            case 1:
                playQuiz();
                break;
            case 2:
                printf("Exiting the game. Goodbye and God bless you!\n");
                return 0; // Exit the program
            default:
                printf("Invalid choice. Please select a valid option.\n");
        }
    }

    return 0;
}

void displayMenu() {
    printf("\n=== Bible Quiz Game Menu ===\n");
    printf("1. Start Quiz\n");
    printf("2. Exit\n");
}

void playQuiz() {
    int score = 0, userAnswer;

    printf("\nStarting the Bible Quiz...\n");

    // Question 1
    printf("\n1. Who built the ark to survive the great flood?\n");
    printf("1) Abraham\n2) Moses\n3) Noah\n4) David\n");
    printf("Your answer: ");
    scanf("%d", &userAnswer);
    score += checkAnswer(userAnswer, 3);

    // Question 2
    printf("\n2. Who was thrown into the lions’ den for praying to God?\n");
    printf("1) Jonah\n2) Daniel\n3) Elijah\n4) Joseph\n");
    printf("Your answer: ");
    scanf("%d", &userAnswer);
    score += checkAnswer(userAnswer, 2);

    // Question 3
    printf("\n3. Who betrayed Jesus for 30 pieces of silver?\n");
    printf("1) Judas Iscariot\n2) Peter\n3) John\n4) Thomas\n");
    printf("Your answer: ");
    scanf("%d", &userAnswer);
    score += checkAnswer(userAnswer, 1);

    // Question 4
    printf("\n4. Who parted the Red Sea?\n");
    printf("1) Joshua\n2) Moses\n3) Aaron\n4) David\n");
    printf("Your answer: ");
    scanf("%d", &userAnswer);
    score += checkAnswer(userAnswer, 2);

    // Question 5
    printf("\n5. Who was swallowed by a great fish?\n");
    printf("1) Jonah\n2) Paul\n3) Job\n4) Noah\n");
    printf("Your answer: ");
    scanf("%d", &userAnswer);
    score += checkAnswer(userAnswer, 1);

    // Final Score
    printf("\nQuiz Complete! Your final score is: %d out of 5\n", score);
    if (score == 5) {
        printf("Excellent! You know your Bible very well!\n");
    } else if (score >= 3) {
        printf("Good job! You scored %d out of 5. Keep studying the Word!\n", score);
    } else {
        printf("You scored %d out of 5. Keep learning more about the Bible!\n", score);
    }
}

int checkAnswer(int userAnswer, int correctAnswer) {
    if (userAnswer == correctAnswer) {
        printf("Correct!\n");
        return 1; 
    } else {
        printf("Wrong! The correct answer was %d.\n", correctAnswer);
        return 0;
    }
}