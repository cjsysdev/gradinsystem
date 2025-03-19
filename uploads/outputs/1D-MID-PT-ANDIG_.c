#include <stdio.h>

void displayMenu();
void addGrade(float grades[], int *count);
void calculateAverage(float grades[], int count);
void showGrades(float grades[], int count);

int main(){
    float grades[100];
    int count = 0;
    int choice;
    int running = 1;

    while (running) {
        displayMenu();
        scanf("%d", &choice);

        switch (choice) {
            case 1:
                addGrade(grades, &count);
                break;
            case 2:
                calculateAverage(grades, count);
                break;
            case 3:
                showGrades(grades, count);
                break;
            case 4:
                printf("Exiting Grade Management System. Goodbye!\n");
                running = 0;
                break;
            default:
                printf("Invalid choice. Please try again.\n");
        }
    }

    return 0;
}

void displayMenu() {
    printf("\n--- Grade Management System ---\n");
    printf("1. Add Student Grade\n");
    printf("2. Calculate Average Grade\n");
    printf("3. Show All Grades\n");
    printf("4. Exit\n");
    printf("Enter your choice: ");
}

void addGrade(float grades[], int *count){
    float grade;
    printf("Enter student's grade (0-100): ");
    scanf("%f", &grade);

    if (grade >= 0 && grade <= 100) {
        grades[*count] = grade;
        (*count)++;
        printf("Grade added successfully!\n");
    } else {
        printf("Invalid grade. Please enter a number between 0 and 100.\n");
    }
}

void calculateAverage(float grades[], int count){
    if (count == 0) {
        printf("No grades available to calculate average.\n");
        return;
    }

    float sum = 0;
    for (int i = 0; i < count; i++){
        sum += grades[i];
    }

    float average = sum / count;
    printf("Average Grade: %.2f\n", average);

    if (average >= 75) {
        printf("Status: PASS\n");
    } else {
        printf("Status: FAIL\n");
    }
}

void showGrades(float grades[], int count){
    if (count == 0) {
        printf("No grades recorded.\n");
        return;
    }

    printf("Student Grades:\n");
    for (int i = 0; i < count; i++){
        printf("Student %d: %.2f\n", i + 1, grades[i]);
    }
}
    