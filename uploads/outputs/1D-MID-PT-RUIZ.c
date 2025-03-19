#include <stdio.h>
#include <string.h>

#define MAX_STUDENTS 50

typedef struct {
    int id;
    char name[50];
    float grade;
} Student;

void displayMenu();
void addStudent(Student students[], int *count);
void showStudents(Student students[], int count);
void searchStudent(Student students[], int count);

int main() {
    Student students[MAX_STUDENTS];
    int count = 0;
    int choice;
    int running = 1;

    while (running) {
        displayMenu();
        scanf("%d", &choice);

        switch (choice) {
            case 1:
                addStudent(students, &count);
                break;
            case 2:
                showStudents(students, count);
                break;
            case 3:
                searchStudent(students, count);
                break;
            case 4:
                printf("Exiting Student Management System. Goodbye!\n");
                running = 0;
                break;
            default:
                printf("Invalid choice. Please try again.\n");
        }
    }

    return 0;
}

void displayMenu() {
    printf("\n--- Student Management System ---\n");
    printf("1. Add Student\n");
    printf("2. Show All Students\n");
    printf("3. Search Student by ID\n");
    printf("4. Exit\n");
    printf("Enter your choice: ");
}

void addStudent(Student students[], int *count) {
    if (*count >= MAX_STUDENTS) {
        printf("Student list is full!\n");
        return;
    }

    printf("Enter Student ID: ");
    scanf("%d", &students[*count].id);
    printf("Enter Student Name: ");
    scanf(" %[^\n]", students[*count].name);
    printf("Enter Student Grade: ");
    scanf("%f", &students[*count].grade);

    (*count)++;
    printf("Student added successfully!\n");
}

void showStudents(Student students[], int count) {
    if (count == 0) {
        printf("No students recorded.\n");
        return;
    }

    printf("\nStudent Records:\n");
    for (int i = 0; i < count; i++) {
        printf("ID: %d, Name: %s, Grade: %.2f\n", students[i].id, students[i].name, students[i].grade);
    }
}

void searchStudent(Student students[], int count) {
    if (count == 0) {
        printf("No students recorded.\n");
        return;
    }

    int searchID;
    printf("Enter Student ID to search: ");
    scanf("%d", &searchID);

    for (int i = 0; i < count; i++) {
        if (students[i].id == searchID) {
            printf("Student Found: ID: %d, Name: %s, Grade: %.2f\n", students[i].id, students[i].name, students[i].grade);
            return;
        }
    }

    printf("Student with ID %d not found.\n", searchID);
}
    