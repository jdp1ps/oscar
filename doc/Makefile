BUILD = build
BOOKNAME = Documentation-Oscar
TITLE = title.txt
CHAPTERS = install-prod.md connectors.md oscar-commands.md
TOC = --toc --toc-depth=2
LATEX_CLASS = report

all: book

book: pdf

clean:
	rm -r $(BUILD)

pdf: $(BUILD)/pdf/$(BOOKNAME).pdf

$(BUILD)/pdf/$(BOOKNAME).pdf: $(TITLE) $(CHAPTERS)
	mkdir -p $(BUILD)/pdf
	pandoc $(TOC) --latex-engine=xelatex -V documentclass=$(LATEX_CLASS) -o $@ $^

.PHONY: all book clean pdf
