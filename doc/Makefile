BUILD = build
BOOKNAME = Documentation-Oscar
TITLE = title.txt
CHAPTERS = install-prod.md update.md connectors.md oscar-commands.md dev.md privileges.md
TOC = --toc --toc-depth=3
LATEX_CLASS = report

all: book

book: pdf

clean:
	rm -r $(BUILD)

pdf: $(BUILD)/pdf/$(BOOKNAME).pdf

$(BUILD)/pdf/$(BOOKNAME).pdf: $(TITLE) $(CHAPTERS)
	mkdir -p $(BUILD)/pdf
	pandoc $(TOC) --latex-engine=xelatex -V --template=template.tex -o $@ $^

.PHONY: all book clean pdf
